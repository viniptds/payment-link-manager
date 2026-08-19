<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsCompanyData;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase, BuildsCompanyData;

    private $company;
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway();
        $this->company = $this->company();
        $this->admin = $this->user(['is_admin' => true, 'company_id' => $this->company->id]);
    }

    public function test_guests_cannot_reach_the_customers(): void
    {
        $this->get('/customers')->assertRedirect('/login');
    }

    public function test_customers_of_the_company_are_listed(): void
    {
        $mine = $this->customer(['name' => 'Cliente Proprio', 'company_id' => $this->company->id]);
        $this->customer(['name' => 'Cliente Alheio', 'company_id' => $this->company()->id]);

        $this->actingAs($this->admin)->get('/customers')
            ->assertOk()
            ->assertSee('Cliente Proprio')
            ->assertSee($mine->email)
            ->assertDontSee('Cliente Alheio');
    }

    public function test_customer_is_shown_with_its_data(): void
    {
        $customer = $this->customer([
            'name' => 'Cliente Proprio',
            'cpf' => '11144477735',
            'document' => 'OAB-1234',
            'company_id' => $this->company->id,
        ]);

        $this->actingAs($this->admin)->get('/customers/' . $customer->id)
            ->assertOk()
            ->assertSee('Cliente Proprio')
            ->assertSee('11144477735')
            ->assertSee('OAB-1234');
    }

    public function test_customer_of_another_company_is_out_of_reach(): void
    {
        $stranger = $this->customer(['company_id' => $this->company()->id]);

        $this->actingAs($this->admin)->get('/customers/' . $stranger->id)->assertForbidden();
    }

    public function test_super_admin_reaches_the_customers_of_every_company(): void
    {
        $superAdmin = $this->superAdmin();
        $customer = $this->customer(['name' => 'Cliente Alheio', 'company_id' => $this->company->id]);

        $this->actingAs($superAdmin)->get('/customers')->assertOk()->assertSee('Cliente Alheio');
        $this->actingAs($superAdmin)->get('/customers/' . $customer->id)->assertOk();
    }

    /* ------------------------------------------------------------------ *
     * Public checkout, where customers are created
     * ------------------------------------------------------------------ */

    private function personalPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Pagador',
            'email' => 'pagador@example.com',
            'cpf' => '111.444.777-35',
            'document' => '12345',
        ], $overrides);
    }

    public function test_customer_is_created_by_the_public_checkout(): void
    {
        $payment = $this->payment(['company_id' => $this->company->id]);

        $this->post('/pay/' . $payment->id . '/personal', $this->personalPayload())
            ->assertRedirect('/pay/' . $payment->id . '?page=card');

        $customer = Customer::first();

        $this->assertSame('Pagador', $customer->name);
        $this->assertSame('pagador@example.com', $customer->email);
        $this->assertSame('11144477735', $customer->cpf, 'the cpf is stored without punctuation');
        $this->assertSame('12345', $customer->document);
        $this->assertSame($this->company->id, $customer->company_id, 'inherits the company of the payment');
        $this->assertSame($customer->id, $payment->fresh()->customer_id);
    }

    public function test_existing_customer_is_updated_instead_of_duplicated(): void
    {
        $payment = $this->payment(['company_id' => $this->company->id]);
        $customer = $this->customer(['name' => 'Nome Antigo', 'company_id' => $this->company->id]);

        $this->post('/pay/' . $payment->id . '/personal', $this->personalPayload([
            'customer_id' => $customer->id,
            'name' => 'Nome Novo',
        ]))->assertRedirect();

        $this->assertSame(1, Customer::count());
        $this->assertSame('Nome Novo', $customer->fresh()->name);
    }

    public function test_customer_of_a_payment_without_company_uses_the_request_company(): void
    {
        config(['app.url' => 'https://mydomain.com']);
        $fallback = $this->company(['name' => 'Principal', 'slug' => 'principal']);
        $acme = $this->company(['name' => 'Acme', 'slug' => 'acme']);

        $payment = $this->payment(['company_id' => null]);

        $this->post('https://acme.mydomain.com/pay/' . $payment->id . '/personal', $this->personalPayload())
            ->assertRedirect();

        $this->assertSame($acme->id, Customer::first()->company_id);
    }

    /**
     * @dataProvider invalidPersonalDataProvider
     */
    public function test_personal_data_is_validated(array $payload, string $field): void
    {
        $payment = $this->payment(['company_id' => $this->company->id]);

        $this->post('/pay/' . $payment->id . '/personal', $this->personalPayload($payload))
            ->assertSessionHasErrors($field);

        $this->assertSame(0, Customer::count());
    }

    public static function invalidPersonalDataProvider(): array
    {
        return [
            'without name' => [['name' => ''], 'name'],
            'without email' => [['email' => ''], 'email'],
            'invalid email' => [['email' => 'nao-e-email'], 'email'],
            'without cpf' => [['cpf' => ''], 'cpf'],
            'invalid cpf' => [['cpf' => '111.111.111-11'], 'cpf'],
            'without document' => [['document' => ''], 'document'],
            'non numeric document' => [['document' => 'abc'], 'document'],
            'unknown customer' => [['customer_id' => 'e5b8f2a0-0000-4000-8000-000000000000'], 'customer_id'],
        ];
    }

    public function test_the_card_page_needs_a_customer(): void
    {
        $payment = $this->payment(['company_id' => $this->company->id]);

        $this->get('/pay/' . $payment->id . '?page=card')
            ->assertRedirect('pay/' . $payment->id);
    }

    public function test_the_payment_page_is_public(): void
    {
        $payment = $this->payment(['description' => 'Anuidade 2026', 'company_id' => $this->company->id]);

        $this->get('/pay/' . $payment->id)
            ->assertOk()
            ->assertSee('Anuidade 2026');
    }
}
