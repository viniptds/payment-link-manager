<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsCompanyData;
use Tests\TestCase;

class CompanySiteTest extends TestCase
{
    use RefreshDatabase, BuildsCompanyData;

    private $fallback;
    private $acme;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'https://mydomain.com']);

        $this->gateway();

        $this->fallback = $this->company([
            'name' => 'Principal',
            'slug' => 'principal',
            'site_title' => 'Titulo Principal',
            'logo_url' => 'https://cdn.principal.com/logo.png',
        ]);

        $this->acme = $this->company([
            'name' => 'Acme',
            'slug' => 'acme',
            'site_title' => 'Acme Pagamentos',
            'logo_url' => 'https://cdn.acme.com/logo.png',
        ]);
    }

    /* ------------------------------------------------------------------ *
     * Settings of the company serving the request
     * ------------------------------------------------------------------ */

    public function test_the_settings_of_the_subdomain_company_are_applied(): void
    {
        $this->get('https://acme.mydomain.com/');

        $this->assertSame($this->acme->id, Company::current()->id);
        $this->assertSame('Acme Pagamentos', config('settings.app_name'));
        $this->assertSame('https://cdn.acme.com/logo.png', config('settings.logo_url'));
        $this->assertSame('acme', config('settings.company_slug'));
    }

    /**
     * @dataProvider unreachableHostProvider
     */
    public function test_unreachable_hosts_are_served_by_the_fallback_company(string $host): void
    {
        $this->get($host . '/');

        $this->assertSame($this->fallback->id, Company::current()->id);
        $this->assertSame('Titulo Principal', config('settings.app_name'));
    }

    public static function unreachableHostProvider(): array
    {
        return [
            'application domain' => ['https://mydomain.com'],
            'unknown slug' => ['https://desconhecida.mydomain.com'],
            'reserved subdomain' => ['https://www.mydomain.com'],
        ];
    }

    public function test_inactive_company_is_served_by_the_fallback_company(): void
    {
        $this->company(['name' => 'Inativa', 'slug' => 'inativa', 'status' => false, 'site_title' => 'Titulo Inativo']);

        $this->get('https://inativa.mydomain.com/');

        $this->assertSame($this->fallback->id, Company::current()->id);
        $this->assertSame('Titulo Principal', config('settings.app_name'));
    }

    /* ------------------------------------------------------------------ *
     * Branding of the pages
     * ------------------------------------------------------------------ */

    public function test_the_home_page_is_branded_by_the_company(): void
    {
        $this->get('https://acme.mydomain.com/')
            ->assertOk()
            ->assertSee('Acme Pagamentos')
            ->assertSee('https://cdn.acme.com/logo.png')
            ->assertDontSee('Titulo Principal');
    }

    public function test_the_panel_is_branded_by_the_company(): void
    {
        $user = $this->user(['is_admin' => true, 'company_id' => $this->acme->id]);

        $this->actingAs($user)->get('https://acme.mydomain.com/dashboard')
            ->assertOk()
            ->assertSee('<title>Acme Pagamentos</title>', false)
            ->assertSee('https://cdn.acme.com/logo.png');
    }

    public function test_the_login_page_is_branded_by_the_company(): void
    {
        $this->get('https://acme.mydomain.com/login')
            ->assertOk()
            ->assertSee('<title>Acme Pagamentos</title>', false);
    }

    public function test_the_public_payment_page_shows_the_company_as_the_payee(): void
    {
        $payment = $this->payment(['company_id' => $this->acme->id]);

        $this->get('https://acme.mydomain.com/pay/' . $payment->id)
            ->assertOk()
            ->assertSee('Acme Pagamentos');
    }

    /* ------------------------------------------------------------------ *
     * Origin of the records
     * ------------------------------------------------------------------ */

    public function test_payments_belong_to_the_company_of_the_operator(): void
    {
        $user = $this->user(['is_admin' => true, 'company_id' => $this->acme->id]);

        // the company of the operator wins over the host
        $this->actingAs($user)->post('https://mydomain.com/payments', [
            'value' => '10.00',
            'description' => 'Anuidade',
        ])->assertRedirect();

        $this->assertSame($this->acme->id, Payment::first()->company_id);
    }

    public function test_payments_of_an_operator_without_company_belong_to_the_host_company(): void
    {
        $user = $this->user(['is_admin' => true, 'company_id' => null]);

        $this->actingAs($user)->post('https://acme.mydomain.com/payments', [
            'value' => '10.00',
            'description' => 'Anuidade',
        ])->assertRedirect();

        $this->assertSame($this->acme->id, Payment::first()->company_id);
    }

    public function test_records_of_an_unreachable_host_belong_to_the_fallback_company(): void
    {
        $user = $this->user(['is_admin' => true, 'company_id' => null]);

        $this->actingAs($user)->post('https://desconhecida.mydomain.com/payments', [
            'value' => '10.00',
            'description' => 'Anuidade',
        ])->assertRedirect();

        $payment = Payment::first();
        $this->assertSame($this->fallback->id, $payment->company_id);

        $this->post('https://desconhecida.mydomain.com/pay/' . $payment->id . '/personal', [
            'name' => 'Pagador',
            'email' => 'pagador@example.com',
            'cpf' => '111.444.777-35',
            'document' => '12345',
        ])->assertRedirect();

        $this->assertSame($this->fallback->id, Customer::first()->company_id);
    }

    public function test_operators_belong_to_the_host_company(): void
    {
        $admin = $this->user(['is_admin' => true, 'company_id' => null]);

        $this->actingAs($admin)->post('https://acme.mydomain.com/users', [
            'name' => 'Operador',
            'email' => 'operador@example.com',
            'password' => 'senha-secreta',
            'password_confirmation' => 'senha-secreta',
            'is_admin' => '0',
        ])->assertRedirect('users');

        $this->assertSame($this->acme->id, \App\Models\User::where('email', 'operador@example.com')->first()->company_id);
    }

    /* ------------------------------------------------------------------ *
     * Without companies at all
     * ------------------------------------------------------------------ */

    public function test_the_application_works_without_any_company(): void
    {
        Company::query()->delete();

        $this->get('https://mydomain.com/')->assertOk();
        $this->assertNull(Company::current());
    }
}
