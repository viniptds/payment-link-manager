<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsCompanyData;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase, BuildsCompanyData;

    public function test_id_is_a_generated_uuid(): void
    {
        $customer = $this->customer();

        $this->assertFalse($customer->getIncrementing());
        $this->assertSame('string', $customer->getKeyType());
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $customer->id);
    }

    public function test_given_id_is_kept(): void
    {
        $customer = $this->customer(['id' => 'e5b8f2a0-0000-4000-8000-000000000000']);

        $this->assertSame('e5b8f2a0-0000-4000-8000-000000000000', $customer->id);
    }

    public function test_fields_are_mass_assignable(): void
    {
        $company = $this->company();

        $customer = Customer::create([
            'name' => 'Cliente',
            'email' => 'cliente@example.com',
            'cpf' => '11144477735',
            'document' => '12345',
            'company_id' => $company->id,
        ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Cliente',
            'email' => 'cliente@example.com',
            'cpf' => '11144477735',
            'document' => '12345',
            'company_id' => $company->id,
        ]);
    }

    public function test_document_is_optional(): void
    {
        $this->assertNull($this->customer()->document);
    }

    public function test_company_relation(): void
    {
        $company = $this->company();

        $this->assertSame($company->id, $this->customer(['company_id' => $company->id])->company->id);
        $this->assertNull($this->customer()->company);
    }

    public function test_payments_point_back_to_the_customer(): void
    {
        $customer = $this->customer();

        $payment = $this->payment();
        $payment->customer_id = $customer->id;
        $payment->save();

        $this->assertSame($customer->id, $payment->fresh()->customer->id);
        $this->assertSame(1, Payment::where('customer_id', $customer->id)->count());
    }

    /* ------------------------------------------------------------------ *
     * Company filter
     * ------------------------------------------------------------------ */

    public function test_visible_to_keeps_only_the_customers_of_the_company(): void
    {
        $acme = $this->company();
        $other = $this->company();

        $user = $this->user(['company_id' => $acme->id]);
        $mine = $this->customer(['company_id' => $acme->id]);
        $this->customer(['company_id' => $other->id]);

        $this->assertSame([$mine->id], Customer::visibleTo($user)->pluck('id')->all());
        $this->assertSame(2, Customer::visibleTo($this->superAdmin())->count());
        $this->assertSame(2, Customer::visibleTo($this->mainUser($acme))->count());
    }

    public function test_is_visible_to(): void
    {
        $acme = $this->company();
        $user = $this->user(['company_id' => $acme->id]);

        $this->assertTrue($this->customer(['company_id' => $acme->id])->isVisibleTo($user));
        $this->assertFalse($this->customer(['company_id' => $this->company()->id])->isVisibleTo($user));
        $this->assertFalse($this->customer()->isVisibleTo(null));
    }
}
