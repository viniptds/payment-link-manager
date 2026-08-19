<?php

namespace Tests\Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsCompanyData;
use Tests\TestCase;

class CompanyManagementTest extends TestCase
{
    use RefreshDatabase, BuildsCompanyData;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Empresa Nova',
            'slug' => 'empresa-nova',
            'site_title' => 'Empresa Nova Pagamentos',
            'logo_url' => 'https://cdn.example.com/logo.png',
            'status' => '1',
            'main_user' => '',
        ], $overrides);
    }

    /* ------------------------------------------------------------------ *
     * Permission
     * ------------------------------------------------------------------ */

    public function test_guests_are_sent_to_the_login(): void
    {
        $this->get('/companies')->assertRedirect('/login');
    }

    public function test_regular_users_cannot_reach_the_companies(): void
    {
        $this->actingAs($this->user(['is_admin' => true, 'company_id' => $this->company()->id]))
            ->get('/companies')
            ->assertForbidden();
    }

    public function test_super_admin_and_main_user_reach_the_companies(): void
    {
        $company = $this->company();

        $this->actingAs($this->superAdmin())->get('/companies')->assertOk();
        $this->actingAs($this->mainUser($company))->get('/companies')->assertOk();
    }

    public function test_the_link_is_only_in_the_menu_of_who_can_manage_companies(): void
    {
        $company = $this->company();

        $this->actingAs($this->mainUser($company))->get('/dashboard')->assertSee('companies');
        $this->actingAs($this->user(['is_admin' => true, 'company_id' => $company->id]))
            ->get('/dashboard')
            ->assertDontSee('companies');
    }

    /* ------------------------------------------------------------------ *
     * Creation
     * ------------------------------------------------------------------ */

    public function test_company_is_created(): void
    {
        $admin = $this->superAdmin();
        $owner = $this->user();

        $this->actingAs($admin)
            ->post('/companies', $this->validPayload(['main_user' => $owner->id]))
            ->assertSessionHasNoErrors();

        $company = Company::where('slug', 'empresa-nova')->first();

        $this->assertNotNull($company);
        $this->assertSame('Empresa Nova', $company->name);
        $this->assertSame('Empresa Nova Pagamentos', $company->site_title);
        $this->assertSame('https://cdn.example.com/logo.png', $company->logo_url);
        $this->assertTrue($company->status);
        $this->assertSame($owner->id, $company->main_user);
    }

    public function test_inactive_company_is_created(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/companies', $this->validPayload(['status' => '0']))
            ->assertSessionHasNoErrors();

        $this->assertFalse(Company::where('slug', 'empresa-nova')->first()->status);
    }

    public function test_optional_site_data_can_be_left_empty(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/companies', $this->validPayload(['site_title' => '', 'logo_url' => '']))
            ->assertSessionHasNoErrors();

        $company = Company::where('slug', 'empresa-nova')->first();

        $this->assertNull($company->site_title);
        $this->assertNull($company->logo_url);
    }

    /**
     * @dataProvider invalidCompanyProvider
     */
    public function test_company_is_validated(array $payload, string $field): void
    {
        $admin = $this->superAdmin();
        $before = Company::count();

        $this->actingAs($admin)
            ->post('/companies', $this->validPayload($payload))
            ->assertSessionHasErrors($field);

        $this->assertSame($before, Company::count());
    }

    public static function invalidCompanyProvider(): array
    {
        return [
            'without name' => [['name' => ''], 'name'],
            'name too long' => [['name' => str_repeat('a', 256)], 'name'],
            'without slug' => [['slug' => ''], 'slug'],
            'slug with spaces' => [['slug' => 'empresa nova'], 'slug'],
            'slug with uppercase' => [['slug' => 'EmpresaNova'], 'slug'],
            'slug with underscore' => [['slug' => 'empresa_nova'], 'slug'],
            'slug too long' => [['slug' => str_repeat('a', 64)], 'slug'],
            'reserved slug www' => [['slug' => 'www'], 'slug'],
            'reserved slug admin' => [['slug' => 'admin'], 'slug'],
            'without status' => [['status' => ''], 'status'],
            'invalid status' => [['status' => '2'], 'status'],
            'unknown main user' => [['main_user' => 'e5b8f2a0-0000-4000-8000-000000000000'], 'main_user'],
            'site title too long' => [['site_title' => str_repeat('a', 256)], 'site_title'],
        ];
    }

    public function test_name_and_slug_are_unique(): void
    {
        $admin = $this->superAdmin();
        $this->company(['name' => 'Empresa Nova', 'slug' => 'empresa-nova']);

        $this->actingAs($admin)
            ->post('/companies', $this->validPayload(['slug' => 'outro-slug']))
            ->assertSessionHasErrors('name');

        $this->actingAs($admin)
            ->post('/companies', $this->validPayload(['name' => 'Outro Nome']))
            ->assertSessionHasErrors('slug');
    }

    /* ------------------------------------------------------------------ *
     * Update
     * ------------------------------------------------------------------ */

    public function test_company_is_updated(): void
    {
        $admin = $this->superAdmin();
        $company = $this->company();
        $owner = $this->user();

        $this->actingAs($admin)->patch('/companies/' . $company->id, $this->validPayload([
            'name' => 'Nome Editado',
            'slug' => 'nome-editado',
            'site_title' => 'Titulo Editado',
            'status' => '0',
            'main_user' => $owner->id,
        ]))->assertRedirect('companies/' . $company->id)->assertSessionHas('editMessage');

        $company->refresh();

        $this->assertSame('Nome Editado', $company->name);
        $this->assertSame('nome-editado', $company->slug);
        $this->assertSame('Titulo Editado', $company->site_title);
        $this->assertFalse($company->status);
        $this->assertSame($owner->id, $company->main_user);
    }

    public function test_company_keeps_its_own_name_and_slug_on_update(): void
    {
        $admin = $this->superAdmin();
        $company = $this->company(['name' => 'Empresa Nova', 'slug' => 'empresa-nova']);

        $this->actingAs($admin)
            ->patch('/companies/' . $company->id, $this->validPayload())
            ->assertSessionHasNoErrors();
    }

    public function test_the_main_user_can_be_cleared(): void
    {
        $admin = $this->superAdmin();
        $company = $this->company();
        $this->mainUser($company);

        $this->actingAs($admin)
            ->patch('/companies/' . $company->id, $this->validPayload(['main_user' => '']))
            ->assertSessionHasNoErrors();

        $this->assertNull($company->fresh()->main_user);
    }

    /* ------------------------------------------------------------------ *
     * Removal
     * ------------------------------------------------------------------ */

    public function test_company_without_records_is_removed(): void
    {
        $admin = $this->superAdmin();
        $company = $this->company();

        $this->actingAs($admin)->get('/companies/' . $company->id . '/delete')
            ->assertRedirect('companies')
            ->assertSessionHas('message');

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }

    /**
     * @dataProvider relatedRecordProvider
     */
    public function test_company_with_records_is_not_removed(string $relation): void
    {
        $admin = $this->superAdmin();
        $company = $this->company();

        match ($relation) {
            'user' => $this->user(['company_id' => $company->id]),
            'customer' => $this->customer(['company_id' => $company->id]),
            'payment' => $this->payment(['company_id' => $company->id]),
        };

        $this->actingAs($admin)->get('/companies/' . $company->id . '/delete')->assertRedirect('companies');

        $this->assertDatabaseHas('companies', ['id' => $company->id]);
    }

    public static function relatedRecordProvider(): array
    {
        return [['user'], ['customer'], ['payment']];
    }

    /* ------------------------------------------------------------------ *
     * Visibility of the companies themselves
     * ------------------------------------------------------------------ */

    public function test_main_user_only_manages_its_own_companies(): void
    {
        $own = $this->company(['name' => 'Empresa Propria']);
        $foreign = $this->company(['name' => 'Empresa Alheia']);
        $mainUser = $this->mainUser($own);

        $this->actingAs($mainUser)->get('/companies')
            ->assertOk()
            ->assertSee('Empresa Propria')
            ->assertDontSee('Empresa Alheia');

        $this->actingAs($mainUser)->get('/companies/' . $own->id)->assertOk();
        $this->actingAs($mainUser)->get('/companies/' . $foreign->id)->assertForbidden();
        $this->actingAs($mainUser)->patch('/companies/' . $foreign->id, $this->validPayload())->assertForbidden();
        $this->actingAs($mainUser)->get('/companies/' . $foreign->id . '/delete')->assertForbidden();

        $this->assertDatabaseHas('companies', ['id' => $foreign->id, 'name' => 'Empresa Alheia']);
    }

    public function test_super_admin_manages_every_company(): void
    {
        $admin = $this->superAdmin();
        $this->company(['name' => 'Empresa Um']);
        $this->company(['name' => 'Empresa Dois']);

        $this->actingAs($admin)->get('/companies')
            ->assertOk()
            ->assertSee('Empresa Um')
            ->assertSee('Empresa Dois');
    }

    /* ------------------------------------------------------------------ *
     * Seeder
     * ------------------------------------------------------------------ */

    public function test_the_seeder_creates_the_fallback_company(): void
    {
        (new \Database\Seeders\CompanySeeder)->run();

        $company = Company::find(Company::FALLBACK_ID);

        $this->assertNotNull($company);
        $this->assertNotEmpty($company->name);
        $this->assertNotEmpty($company->slug);
        $this->assertNotEmpty($company->site_title);
        $this->assertTrue($company->status);
    }

    public function test_the_seeder_can_run_twice(): void
    {
        (new \Database\Seeders\CompanySeeder)->run();
        (new \Database\Seeders\CompanySeeder)->run();

        $this->assertSame(1, Company::count());
    }

    public function test_the_subdomain_of_the_company_is_listed(): void
    {
        config(['app.url' => 'https://mydomain.com']);
        $admin = $this->superAdmin();
        $company = $this->company(['slug' => 'acme']);

        $this->actingAs($admin)->get('/companies')->assertOk()->assertSee('acme');
        $this->actingAs($admin)->get('/companies/' . $company->id)
            ->assertOk()
            ->assertSee('https://acme.mydomain.com');
    }
}
