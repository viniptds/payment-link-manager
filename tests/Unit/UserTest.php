<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\BuildsCompanyData;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase, BuildsCompanyData;

    /* ------------------------------------------------------------------ *
     * Attributes
     * ------------------------------------------------------------------ */

    public function test_id_is_a_generated_uuid(): void
    {
        $user = $this->user();

        $this->assertFalse($user->getIncrementing());
        $this->assertSame('string', $user->getKeyType());
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $user->id);
    }

    public function test_password_is_hashed_and_hidden(): void
    {
        $user = User::create([
            'name' => 'Operador',
            'email' => 'operador@example.com',
            'password' => 'senha-secreta',
        ]);

        $this->assertNotSame('senha-secreta', $user->password);
        $this->assertTrue(Hash::check('senha-secreta', $user->password));
        $this->assertArrayNotHasKey('password', $user->toArray());
        $this->assertArrayNotHasKey('remember_token', $user->toArray());
    }

    public function test_company_id_is_mass_assignable(): void
    {
        $company = $this->company();

        $user = User::create([
            'name' => 'Operador',
            'email' => 'operador@example.com',
            'password' => 'senha-secreta',
            'company_id' => $company->id,
            'is_admin' => true,
        ]);

        $this->assertSame($company->id, $user->company->id);
    }

    /* ------------------------------------------------------------------ *
     * Super admin
     * ------------------------------------------------------------------ */

    public function test_super_admin_is_the_user_of_the_configured_id(): void
    {
        $user = $this->user();
        config(['company.super_admin_id' => $user->id]);

        $this->assertTrue($user->isSuperAdmin());
        $this->assertFalse($this->user()->isSuperAdmin());
    }

    public function test_super_admin_id_is_compared_as_a_string(): void
    {
        $user = $this->user(['id' => '1']);
        config(['company.super_admin_id' => 1]);

        $this->assertTrue($user->isSuperAdmin());
    }

    public function test_nobody_is_super_admin_without_configuration(): void
    {
        config(['company.super_admin_id' => null]);

        $this->assertFalse($this->user()->isSuperAdmin());
    }

    /* ------------------------------------------------------------------ *
     * Main user
     * ------------------------------------------------------------------ */

    public function test_main_user_of_a_company(): void
    {
        $company = $this->company();
        $mainUser = $this->mainUser($company);

        $this->assertTrue($mainUser->isMainUser());
        $this->assertSame([$company->id], $mainUser->managedCompanies->pluck('id')->all());

        $regular = $this->user(['company_id' => $company->id]);
        $this->assertFalse($regular->isMainUser());
        $this->assertCount(0, $regular->managedCompanies);
    }

    public function test_main_user_result_is_resolved_once(): void
    {
        $company = $this->company();
        $user = $this->user();

        $this->assertFalse($user->isMainUser());

        $company->update(['main_user' => $user->id]);

        // already resolved for this instance, a fresh one sees the change
        $this->assertFalse($user->isMainUser());
        $this->assertTrue($user->fresh()->isMainUser());
    }

    /* ------------------------------------------------------------------ *
     * Permissions
     * ------------------------------------------------------------------ */

    public function test_only_super_admin_and_main_users_manage_companies(): void
    {
        $company = $this->company();

        $mainUser = $this->mainUser($company);
        $admin = $this->user(['is_admin' => true, 'company_id' => $company->id]);
        $regular = $this->user(['company_id' => $company->id]);
        $superAdmin = $this->superAdmin();

        $this->assertTrue($superAdmin->canManageCompanies());
        $this->assertTrue($mainUser->canManageCompanies());
        $this->assertFalse($admin->canManageCompanies(), 'being admin is not enough');
        $this->assertFalse($regular->canManageCompanies());
    }

    public function test_sees_all_companies_matches_the_company_permission(): void
    {
        $company = $this->company();

        $this->assertTrue($this->superAdmin()->seesAllCompanies());
        $this->assertTrue($this->mainUser($company)->seesAllCompanies());
        $this->assertFalse($this->user(['is_admin' => true, 'company_id' => $company->id])->seesAllCompanies());
    }

    /* ------------------------------------------------------------------ *
     * Company filter
     * ------------------------------------------------------------------ */

    public function test_visible_to_keeps_only_the_users_of_the_company(): void
    {
        $acme = $this->company();
        $other = $this->company();

        $mine = $this->user(['company_id' => $acme->id]);
        $theirs = $this->user(['company_id' => $other->id]);

        $this->assertSame([$mine->id], User::visibleTo($mine)->pluck('id')->all());
        $this->assertSame([$theirs->id], User::visibleTo($theirs)->pluck('id')->all());
    }

    public function test_visible_to_is_not_filtered_for_unrestricted_users(): void
    {
        $acme = $this->company();
        $this->user(['company_id' => $acme->id]);
        $this->user(['company_id' => $this->company()->id]);

        $superAdmin = $this->superAdmin();
        $mainUser = $this->mainUser($acme);

        $this->assertSame(User::count(), User::visibleTo($superAdmin)->count());
        $this->assertSame(User::count(), User::visibleTo($mainUser)->count());
        $this->assertSame(User::count(), User::visibleTo(null)->count(), 'no user means no filter');
    }

    public function test_visible_to_matches_users_without_company(): void
    {
        $loose = $this->user(['company_id' => null]);
        $this->user(['company_id' => $this->company()->id]);

        $this->assertSame([$loose->id], User::visibleTo($loose)->pluck('id')->all());
    }

    public function test_is_visible_to(): void
    {
        $acme = $this->company();
        $other = $this->company();

        $mine = $this->user(['company_id' => $acme->id]);
        $colleague = $this->user(['company_id' => $acme->id]);
        $theirs = $this->user(['company_id' => $other->id]);

        $this->assertTrue($colleague->isVisibleTo($mine));
        $this->assertFalse($theirs->isVisibleTo($mine));
        $this->assertFalse($theirs->isVisibleTo(null));
        $this->assertTrue($theirs->isVisibleTo($this->superAdmin()));
        $this->assertTrue($theirs->isVisibleTo($this->mainUser($acme)));
    }
}
