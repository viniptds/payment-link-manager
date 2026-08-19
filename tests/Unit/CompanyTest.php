<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsCompanyData;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase, BuildsCompanyData;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'https://mydomain.com']);
    }

    /* ------------------------------------------------------------------ *
     * Attributes and relations
     * ------------------------------------------------------------------ */

    public function test_status_is_cast_to_boolean(): void
    {
        $company = $this->company(['status' => 1]);

        $this->assertTrue($company->fresh()->status);
        $this->assertFalse($this->company(['status' => 0])->fresh()->status);
    }

    public function test_main_user_relation(): void
    {
        $company = $this->company();
        $user = $this->mainUser($company);

        $this->assertSame($user->id, $company->fresh()->mainUser->id);
        $this->assertNull($this->company()->mainUser);
    }

    public function test_records_relations(): void
    {
        $company = $this->company();
        $user = $this->user(['company_id' => $company->id]);
        $customer = $this->customer(['company_id' => $company->id]);
        $payment = $this->payment(['company_id' => $company->id, 'created_by' => $user->id]);

        $this->assertSame([$user->id], $company->users->pluck('id')->all());
        $this->assertSame([$customer->id], $company->customers->pluck('id')->all());
        $this->assertSame([$payment->id], $company->payments->pluck('id')->all());
    }

    public function test_has_related_records_covers_every_relation(): void
    {
        $this->assertFalse($this->company()->hasRelatedRecords());

        $withUser = $this->company();
        $this->user(['company_id' => $withUser->id]);
        $this->assertTrue($withUser->hasRelatedRecords());

        $withCustomer = $this->company();
        $this->customer(['company_id' => $withCustomer->id]);
        $this->assertTrue($withCustomer->hasRelatedRecords());

        $withPayment = $this->company();
        $this->payment(['company_id' => $withPayment->id]);
        $this->assertTrue($withPayment->hasRelatedRecords());
    }

    /* ------------------------------------------------------------------ *
     * Slug extracted from the host
     * ------------------------------------------------------------------ */

    /**
     * @dataProvider hostProvider
     */
    public function test_slug_from_host(?string $host, ?string $expected): void
    {
        $this->assertSame($expected, Company::slugFromHost($host));
    }

    public static function hostProvider(): array
    {
        return [
            'subdomain of the app domain' => ['acme.mydomain.com', 'acme'],
            'uppercase host' => ['ACME.MyDomain.COM', 'acme'],
            'hyphenated slug' => ['acme-ltda.mydomain.com', 'acme-ltda'],
            'nested subdomain keeps the first label' => ['acme.painel.mydomain.com', 'acme'],
            'bare app domain' => ['mydomain.com', null],
            'www is reserved' => ['www.mydomain.com', null],
            'app is reserved' => ['app.mydomain.com', null],
            'unknown domain with subdomain' => ['acme.outrodominio.com', 'acme'],
            'unknown domain without subdomain' => ['outrodominio.com', null],
            'ipv4 address' => ['127.0.0.1', null],
            'empty host' => ['', null],
            'null host' => [null, null],
        ];
    }

    public function test_slug_from_host_ignores_the_www_prefix_of_the_app_url(): void
    {
        config(['app.url' => 'https://www.mydomain.com']);

        $this->assertSame('acme', Company::slugFromHost('acme.mydomain.com'));
        $this->assertNull(Company::slugFromHost('mydomain.com'));
    }

    /* ------------------------------------------------------------------ *
     * Company serving a host
     * ------------------------------------------------------------------ */

    public function test_from_host_resolves_the_active_company_of_the_slug(): void
    {
        $fallback = $this->company(['name' => 'Principal', 'slug' => 'principal']);
        $acme = $this->company(['name' => 'Acme', 'slug' => 'acme']);

        $this->assertSame($acme->id, Company::fromHost('acme.mydomain.com')->id);
        $this->assertSame($fallback->id, Company::FALLBACK_ID);
    }

    public function test_from_host_falls_back_when_the_company_is_not_reachable(): void
    {
        $fallback = $this->company(['name' => 'Principal', 'slug' => 'principal']);
        $this->company(['name' => 'Inativa', 'slug' => 'inativa', 'status' => false]);

        $this->assertSame($fallback->id, Company::fromHost('inativa.mydomain.com')->id, 'inactive company');
        $this->assertSame($fallback->id, Company::fromHost('desconhecida.mydomain.com')->id, 'unknown slug');
        $this->assertSame($fallback->id, Company::fromHost('mydomain.com')->id, 'no subdomain');
        $this->assertSame($fallback->id, Company::fromHost('www.mydomain.com')->id, 'reserved subdomain');
    }

    public function test_from_host_returns_null_without_any_company(): void
    {
        $this->assertNull(Company::fromHost('acme.mydomain.com'));
    }

    public function test_fallback_is_the_company_of_the_fallback_id(): void
    {
        $this->assertNull(Company::fallback());

        $company = $this->company();

        $this->assertSame(Company::FALLBACK_ID, $company->id);
        $this->assertSame($company->id, Company::fallback()->id);
    }

    public function test_current_is_null_when_no_request_resolved_a_company(): void
    {
        $this->assertNull(Company::current());
    }

    /* ------------------------------------------------------------------ *
     * Site data
     * ------------------------------------------------------------------ */

    public function test_url_points_at_the_slug_subdomain(): void
    {
        $this->assertSame('https://acme.mydomain.com', $this->company(['slug' => 'acme'])->url());

        // the scheme comes from the application url
        config(['app.url' => 'http://localhost']);
        $this->assertSame('http://outra.localhost', $this->company(['slug' => 'outra'])->url());
    }

    public function test_url_is_null_without_a_slug(): void
    {
        $this->assertNull($this->company(['slug' => null])->url());
    }

    public function test_title_falls_back_to_the_application_settings(): void
    {
        config(['settings.app_name' => 'Titulo Global']);

        $this->assertSame('Acme Pagamentos', $this->company(['site_title' => 'Acme Pagamentos'])->title());
        $this->assertSame('Titulo Global', $this->company(['site_title' => null])->title());
        $this->assertSame('Titulo Global', $this->company(['site_title' => ''])->title());
    }

    public function test_logo_accepts_urls_and_public_paths(): void
    {
        config(['settings.logo_main' => 'logo.png']);

        $this->assertSame(
            'https://cdn.acme.com/logo.png',
            $this->company(['logo_url' => 'https://cdn.acme.com/logo.png'])->logo()
        );
        $this->assertSame(
            asset('logo1.png'),
            $this->company(['logo_url' => 'logo1.png'])->logo()
        );
        $this->assertSame(
            asset('storage/assets/logo.png'),
            $this->company(['logo_url' => null])->logo()
        );
    }

    public function test_settings_are_the_overrides_of_the_company(): void
    {
        $company = $this->company([
            'slug' => 'acme',
            'site_title' => 'Acme Pagamentos',
            'logo_url' => 'https://cdn.acme.com/logo.png',
        ]);

        $this->assertSame([
            'app_name' => 'Acme Pagamentos',
            'logo_url' => 'https://cdn.acme.com/logo.png',
            'company_slug' => 'acme',
        ], $company->settings());
    }
}
