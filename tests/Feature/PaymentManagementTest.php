<?php

namespace Tests\Feature;

use App\Models\GatewayOperation;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsCompanyData;
use Tests\TestCase;

class PaymentManagementTest extends TestCase
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

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'value' => '150.00',
            'description' => 'Anuidade 2026',
            'expire_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'max_installments' => '6',
            'max_installments_type' => Payment::INSTALLMENT_TYPE_MAX,
        ], $overrides);
    }

    public function test_guests_cannot_reach_the_payments(): void
    {
        $this->get('/payments')->assertRedirect('/login');
    }

    /* ------------------------------------------------------------------ *
     * Creation
     * ------------------------------------------------------------------ */

    public function test_payment_is_created_active_for_the_company_of_the_operator(): void
    {
        $this->actingAs($this->admin)->post('/payments', $this->validPayload())
            ->assertRedirect();

        $payment = Payment::first();

        $this->assertSame('Anuidade 2026', $payment->description);
        $this->assertEquals(150.00, $payment->value);
        $this->assertSame(Payment::STATUS_ACTIVE, $payment->status);
        $this->assertSame($this->admin->id, $payment->created_by);
        $this->assertSame($this->company->id, $payment->company_id);
        $this->assertEquals(6, $payment->max_installments);
        $this->assertSame(Payment::INSTALLMENT_TYPE_MAX, $payment->max_installments_type);
    }

    public function test_the_default_gateway_is_attached(): void
    {
        $this->actingAs($this->admin)->post('/payments', $this->validPayload());

        $this->assertSame([1], Payment::first()->gateways->pluck('id')->all());
    }

    public function test_payment_without_expiration_is_accepted(): void
    {
        $this->actingAs($this->admin)
            ->post('/payments', $this->validPayload(['expire_at' => null]))
            ->assertRedirect();

        $this->assertNull(Payment::first()->expire_at);
        $this->assertSame(Payment::STATUS_ACTIVE, Payment::first()->status);
    }

    /**
     * @dataProvider invalidPaymentProvider
     */
    public function test_payment_is_validated(array $payload, string $field): void
    {
        $this->actingAs($this->admin)
            ->post('/payments', $this->validPayload($payload))
            ->assertSessionHasErrors($field);

        $this->assertSame(0, Payment::count());
    }

    public static function invalidPaymentProvider(): array
    {
        return [
            'without value' => [['value' => ''], 'value'],
            'zero value' => [['value' => '0'], 'value'],
            'negative value' => [['value' => '-10.00'], 'value'],
            'value with too many decimals' => [['value' => '10.123'], 'value'],
            'without description' => [['description' => ''], 'description'],
            'description too long' => [['description' => str_repeat('a', 101)], 'description'],
            'expiration in the past' => [['expire_at' => '2020-01-01 10:00:00'], 'expire_at'],
            'installments above the limit' => [['max_installments' => '99'], 'max_installments'],
            'zero installments' => [['max_installments' => '0'], 'max_installments'],
            'unknown installment type' => [['max_installments_type' => 'qualquer'], 'max_installments_type'],
        ];
    }

    /* ------------------------------------------------------------------ *
     * Listing
     * ------------------------------------------------------------------ */

    public function test_operator_only_lists_the_payments_it_created(): void
    {
        $operator = $this->user(['is_admin' => false, 'company_id' => $this->company->id]);

        $this->payment(['description' => 'Do operador', 'company_id' => $this->company->id, 'created_by' => $operator->id]);
        $this->payment(['description' => 'Do admin', 'company_id' => $this->company->id, 'created_by' => $this->admin->id]);

        $this->actingAs($operator)->get('/payments')
            ->assertOk()
            ->assertSee('Do operador')
            ->assertDontSee('Do admin');

        $this->actingAs($this->admin)->get('/payments')
            ->assertOk()
            ->assertSee('Do operador')
            ->assertSee('Do admin');
    }

    public function test_payments_are_searched_by_description(): void
    {
        $this->payment(['description' => 'Anuidade', 'company_id' => $this->company->id]);
        $this->payment(['description' => 'Certidao', 'company_id' => $this->company->id]);

        $this->actingAs($this->admin)->get('/payments?search=Anui')
            ->assertOk()
            ->assertSee('Anuidade')
            ->assertDontSee('Certidao');
    }

    public function test_payments_are_filtered_by_status(): void
    {
        // descriptions that do not collide with the status labels of the filter
        $this->payment(['description' => 'Certidao', 'company_id' => $this->company->id, 'expire_at' => now()->addDay()]);
        $this->payment(['description' => 'Carteira', 'company_id' => $this->company->id, 'expire_at' => now()->subDay()]);
        $this->payment(['description' => 'Anuidade', 'company_id' => $this->company->id, 'status' => Payment::STATUS_PAID]);

        $this->actingAs($this->admin)->get('/payments?status=' . Payment::STATUS_PAID)
            ->assertOk()
            ->assertSee('Anuidade')
            ->assertDontSee('Certidao')
            ->assertDontSee('Carteira');

        $this->actingAs($this->admin)->get('/payments?status=' . Payment::STATUS_EXPIRED)
            ->assertOk()
            ->assertSee('Carteira')
            ->assertDontSee('Anuidade');
    }

    /* ------------------------------------------------------------------ *
     * Update
     * ------------------------------------------------------------------ */

    public function test_active_payment_is_updated(): void
    {
        $payment = $this->payment(['company_id' => $this->company->id, 'created_by' => $this->admin->id]);

        $this->actingAs($this->admin)->patch('/payments/' . $payment->id, $this->validPayload([
            'description' => 'Descricao nova',
            'value' => '200.00',
        ]))->assertRedirect('/payments/' . $payment->id);

        $payment->refresh();

        $this->assertSame('Descricao nova', $payment->description);
        $this->assertEquals(200.00, $payment->value);
    }

    public function test_paid_payment_is_not_updated(): void
    {
        $payment = $this->payment([
            'description' => 'Original',
            'company_id' => $this->company->id,
            'status' => Payment::STATUS_PAID,
        ]);

        $this->actingAs($this->admin)
            ->patch('/payments/' . $payment->id, $this->validPayload(['description' => 'Tentativa']))
            ->assertRedirect('/payments/' . $payment->id)
            ->assertSessionHas('editMessage');

        $this->assertSame('Original', $payment->fresh()->description);
    }

    /* ------------------------------------------------------------------ *
     * Status changes
     * ------------------------------------------------------------------ */

    public function test_payment_is_activated_and_deactivated(): void
    {
        $payment = $this->payment(['company_id' => $this->company->id, 'expire_at' => now()->addDay()]);

        $this->actingAs($this->admin)->get('/payments/' . $payment->id . '/toggle-active')
            ->assertRedirect('payments/' . $payment->id);
        $this->assertSame(Payment::STATUS_INACTIVE, $payment->fresh()->status);

        $this->actingAs($this->admin)->get('/payments/' . $payment->id . '/toggle-active');
        $this->assertSame(Payment::STATUS_ACTIVE, $payment->fresh()->status);
    }

    /**
     * @dataProvider finalStatusProvider
     */
    public function test_final_status_is_not_toggled(string $status): void
    {
        $payment = $this->payment(['company_id' => $this->company->id, 'status' => $status]);

        $this->actingAs($this->admin)->get('/payments/' . $payment->id . '/toggle-active');

        $this->assertSame($status, $payment->fresh()->getRawOriginal('status'));
    }

    public static function finalStatusProvider(): array
    {
        return [
            'paid' => [Payment::STATUS_PAID],
            'cancelled' => [Payment::STATUS_CANCELLED],
            'expired' => [Payment::STATUS_EXPIRED],
        ];
    }

    public function test_active_payment_is_marked_as_paid(): void
    {
        $payment = $this->payment(['company_id' => $this->company->id, 'expire_at' => now()->addDay()]);

        $this->actingAs($this->admin)->get('/payments/' . $payment->id . '/mark-as-paid')
            ->assertRedirect('payments/' . $payment->id)
            ->assertSessionHas('message');

        $payment->refresh();

        $this->assertSame(Payment::STATUS_PAID, $payment->status);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_inactive_payment_is_not_marked_as_paid(): void
    {
        $payment = $this->payment(['company_id' => $this->company->id, 'status' => Payment::STATUS_INACTIVE]);

        $this->actingAs($this->admin)->get('/payments/' . $payment->id . '/mark-as-paid');

        $this->assertSame(Payment::STATUS_INACTIVE, $payment->fresh()->status);
        $this->assertNull($payment->fresh()->paid_at);
    }

    /* ------------------------------------------------------------------ *
     * Removal
     * ------------------------------------------------------------------ */

    public function test_payment_is_removed_with_its_gateways(): void
    {
        $payment = $this->payment(['company_id' => $this->company->id]);
        $payment->gateways()->sync([1]);

        $this->actingAs($this->admin)->get('/payments/' . $payment->id . '/delete')
            ->assertRedirect('payments')
            ->assertSessionHas('message');

        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
        $this->assertDatabaseMissing('payment_gateways', ['payment_id' => $payment->id]);
    }

    public function test_paid_payment_is_not_removed(): void
    {
        $payment = $this->payment(['company_id' => $this->company->id, 'status' => Payment::STATUS_PAID]);

        $this->actingAs($this->admin)->get('/payments/' . $payment->id . '/delete')
            ->assertRedirect('payments');

        $this->assertDatabaseHas('payments', ['id' => $payment->id]);
    }

    /* ------------------------------------------------------------------ *
     * Void
     * ------------------------------------------------------------------ */

    public function test_void_of_a_payment_that_was_not_paid_is_refused(): void
    {
        $payment = $this->payment(['company_id' => $this->company->id]);

        $this->actingAs($this->admin)->put('/payments/' . $payment->id . '/void')
            ->assertStatus(403)
            ->assertJsonPath('status', false)
            ->assertJsonPath('message', 'O pagamento ainda não foi pago para ser cancelado');
    }

    public function test_void_of_a_cancelled_payment_is_refused(): void
    {
        $payment = $this->payment(['company_id' => $this->company->id, 'status' => Payment::STATUS_CANCELLED]);

        $this->actingAs($this->admin)->put('/payments/' . $payment->id . '/void')
            ->assertStatus(403)
            ->assertJsonPath('message', 'O pagamento já foi estornado');
    }

    public function test_void_needs_a_pay_operation_as_the_latest_one(): void
    {
        $payment = $this->payment(['company_id' => $this->company->id, 'status' => Payment::STATUS_PAID]);
        $this->gatewayOperation($payment, GatewayOperation::VOID_OPERATION);

        $this->actingAs($this->admin)->put('/payments/' . $payment->id . '/void')
            ->assertStatus(403)
            ->assertJsonPath('message', 'O último status do pagamento não é válido');
    }

    public function test_void_needs_the_transaction_data(): void
    {
        $payment = $this->payment(['company_id' => $this->company->id, 'status' => Payment::STATUS_PAID]);
        $this->gatewayOperation($payment, GatewayOperation::PAY_OPERATION, true, ['sem' => 'paymentId']);

        $this->actingAs($this->admin)->put('/payments/' . $payment->id . '/void')
            ->assertStatus(403)
            ->assertJsonPath('message', 'O pagamento não possui dados de transação');
    }

    /* ------------------------------------------------------------------ *
     * Company boundary
     * ------------------------------------------------------------------ */

    public function test_payment_of_another_company_is_out_of_reach(): void
    {
        $stranger = $this->payment(['company_id' => $this->company()->id]);

        $this->actingAs($this->admin)->get('/payments/' . $stranger->id)->assertForbidden();
        $this->actingAs($this->admin)->patch('/payments/' . $stranger->id, $this->validPayload())->assertForbidden();
        $this->actingAs($this->admin)->get('/payments/' . $stranger->id . '/delete')->assertForbidden();
        $this->actingAs($this->admin)->get('/payments/' . $stranger->id . '/toggle-active')->assertForbidden();
        $this->actingAs($this->admin)->get('/payments/' . $stranger->id . '/mark-as-paid')->assertForbidden();
        $this->actingAs($this->admin)->put('/payments/' . $stranger->id . '/void')->assertForbidden();
    }

    public function test_own_payment_is_shown(): void
    {
        $payment = $this->payment(['description' => 'Anuidade', 'company_id' => $this->company->id]);

        $this->actingAs($this->admin)->get('/payments/' . $payment->id)
            ->assertOk()
            ->assertSee('Anuidade');
    }
}
