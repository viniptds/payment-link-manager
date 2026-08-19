<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\BuildsCompanyData;
use Tests\TestCase;

class OperatorTest extends TestCase
{
    use RefreshDatabase, BuildsCompanyData;

    public function test_guests_are_sent_to_the_login(): void
    {
        $this->get('/users')->assertRedirect('/login');
        $this->post('/users', [])->assertRedirect('/login');
    }

    public function test_admin_lists_the_operators_of_its_company(): void
    {
        $acme = $this->company();
        $admin = $this->user(['name' => 'Admin Acme', 'is_admin' => true, 'company_id' => $acme->id]);
        $colleague = $this->user(['name' => 'Operador Acme', 'company_id' => $acme->id]);
        $stranger = $this->user(['name' => 'Operador Outra', 'company_id' => $this->company()->id]);

        $this->actingAs($admin)->get('/users')
            ->assertOk()
            ->assertSee('Admin Acme')
            ->assertSee('Operador Acme')
            ->assertDontSee('Operador Outra');
    }

    public function test_operator_without_permission_does_not_list_operators(): void
    {
        $operator = $this->user(['is_admin' => false, 'company_id' => $this->company()->id]);

        $this->actingAs($operator)->get('/users')->assertForbidden();
    }

    /* ------------------------------------------------------------------ *
     * Creation
     * ------------------------------------------------------------------ */

    public function test_admin_creates_an_operator_of_its_own_company(): void
    {
        $acme = $this->company();
        $admin = $this->user(['is_admin' => true, 'company_id' => $acme->id]);

        $this->actingAs($admin)->post('/users', [
            'name' => 'Novo Operador',
            'email' => 'novo@example.com',
            'password' => 'senha-secreta',
            'password_confirmation' => 'senha-secreta',
            'is_admin' => '0',
        ])->assertRedirect('users');

        $operator = User::where('email', 'novo@example.com')->first();

        $this->assertNotNull($operator);
        $this->assertSame('Novo Operador', $operator->name);
        $this->assertSame($acme->id, $operator->company_id, 'inherits the company of the creator');
        $this->assertEquals(0, $operator->is_admin);
        $this->assertTrue(Hash::check('senha-secreta', $operator->password), 'password is usable');
    }

    public function test_admin_creates_another_admin(): void
    {
        $admin = $this->user(['is_admin' => true, 'company_id' => $this->company()->id]);

        $this->actingAs($admin)->post('/users', [
            'name' => 'Outro Admin',
            'email' => 'admin2@example.com',
            'password' => 'senha-secreta',
            'password_confirmation' => 'senha-secreta',
            'is_admin' => '1',
        ])->assertRedirect('users');

        $this->assertEquals(1, User::where('email', 'admin2@example.com')->first()->is_admin);
    }

    public function test_operator_of_the_request_company_is_used_when_the_creator_has_none(): void
    {
        config(['app.url' => 'https://mydomain.com']);
        $this->company(['name' => 'Principal', 'slug' => 'principal']);
        $acme = $this->company(['name' => 'Acme', 'slug' => 'acme']);

        $admin = $this->user(['is_admin' => true, 'company_id' => null]);

        $this->actingAs($admin)->post('https://acme.mydomain.com/users', [
            'name' => 'Operador Acme',
            'email' => 'operador-acme@example.com',
            'password' => 'senha-secreta',
            'password_confirmation' => 'senha-secreta',
            'is_admin' => '0',
        ])->assertRedirect('users');

        $this->assertSame($acme->id, User::where('email', 'operador-acme@example.com')->first()->company_id);
    }

    /**
     * @dataProvider invalidOperatorProvider
     */
    public function test_operator_is_validated(array $payload, string $field): void
    {
        $admin = $this->user(['is_admin' => true, 'company_id' => $this->company()->id]);
        $before = User::count();

        $this->actingAs($admin)
            ->post('/users', $payload)
            ->assertSessionHasErrors($field);

        $this->assertSame($before, User::count());
    }

    public static function invalidOperatorProvider(): array
    {
        $valid = [
            'name' => 'Novo Operador',
            'email' => 'novo@example.com',
            'password' => 'senha-secreta',
            'password_confirmation' => 'senha-secreta',
            'is_admin' => '0',
        ];

        return [
            'without name' => [array_merge($valid, ['name' => '']), 'name'],
            'without email' => [array_merge($valid, ['email' => '']), 'email'],
            'invalid email' => [array_merge($valid, ['email' => 'nao-e-email']), 'email'],
            'without password' => [array_merge($valid, ['password' => '', 'password_confirmation' => '']), 'password'],
            'password not confirmed' => [array_merge($valid, ['password_confirmation' => 'outra-senha']), 'password'],
            'short password' => [array_merge($valid, ['password' => 'abc', 'password_confirmation' => 'abc']), 'password'],
            'without permission field' => [array_merge($valid, ['is_admin' => '']), 'is_admin'],
            'invalid permission field' => [array_merge($valid, ['is_admin' => '2']), 'is_admin'],
        ];
    }

    public function test_duplicated_email_is_rejected(): void
    {
        $admin = $this->user(['is_admin' => true, 'company_id' => $this->company()->id]);
        $this->user(['email' => 'existente@example.com']);

        $this->actingAs($admin)->post('/users', [
            'name' => 'Novo Operador',
            'email' => 'existente@example.com',
            'password' => 'senha-secreta',
            'password_confirmation' => 'senha-secreta',
            'is_admin' => '0',
        ])->assertSessionHasErrors('email');
    }

    public function test_operator_without_permission_cannot_create(): void
    {
        $operator = $this->user(['is_admin' => false, 'company_id' => $this->company()->id]);

        $this->actingAs($operator)->post('/users', [
            'name' => 'Novo Operador',
            'email' => 'novo@example.com',
            'password' => 'senha-secreta',
            'password_confirmation' => 'senha-secreta',
            'is_admin' => '1',
        ])->assertRedirect('users')->assertSessionHas('message');

        $this->assertDatabaseMissing('users', ['email' => 'novo@example.com']);
    }

    /* ------------------------------------------------------------------ *
     * Permissions
     * ------------------------------------------------------------------ */

    public function test_admin_grants_and_revokes_permissions(): void
    {
        $acme = $this->company();
        $admin = $this->user(['is_admin' => true, 'company_id' => $acme->id]);
        $operator = $this->user(['is_admin' => false, 'company_id' => $acme->id]);

        $this->actingAs($admin)->get('/users/' . $operator->id . '/toggle-admin')->assertRedirect('users');
        $this->assertEquals(1, $operator->fresh()->is_admin);

        $this->actingAs($admin)->get('/users/' . $operator->id . '/toggle-admin')->assertRedirect('users');
        $this->assertEquals(0, $operator->fresh()->is_admin);
    }

    public function test_own_permissions_are_not_changed(): void
    {
        $admin = $this->user(['is_admin' => true, 'company_id' => $this->company()->id]);

        $this->actingAs($admin)->get('/users/' . $admin->id . '/toggle-admin')->assertRedirect('users');

        $this->assertEquals(1, $admin->fresh()->is_admin);
    }

    public function test_permissions_of_another_company_are_not_changed(): void
    {
        $admin = $this->user(['is_admin' => true, 'company_id' => $this->company()->id]);
        $stranger = $this->user(['is_admin' => false, 'company_id' => $this->company()->id]);

        $this->actingAs($admin)->get('/users/' . $stranger->id . '/toggle-admin')->assertForbidden();

        $this->assertEquals(0, $stranger->fresh()->is_admin);
    }

    public function test_super_admin_reaches_the_operators_of_every_company(): void
    {
        $superAdmin = $this->superAdmin();
        $acme = $this->company();
        $operator = $this->user(['name' => 'Operador Acme', 'is_admin' => false, 'company_id' => $acme->id]);

        $this->actingAs($superAdmin)->get('/users')->assertOk()->assertSee('Operador Acme');
        $this->actingAs($superAdmin)->get('/users/' . $operator->id . '/toggle-admin')->assertRedirect('users');

        $this->assertEquals(1, $operator->fresh()->is_admin);
    }
}
