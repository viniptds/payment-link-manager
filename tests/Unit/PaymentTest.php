<?php

namespace Tests\Unit;

use App\Models\GatewayOperation;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsCompanyData;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase, BuildsCompanyData;

    /* ------------------------------------------------------------------ *
     * Attributes
     * ------------------------------------------------------------------ */

    public function test_id_is_a_generated_uuid(): void
    {
        $payment = $this->payment();

        $this->assertFalse($payment->getIncrementing());
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $payment->id);
    }

    public function test_installment_types(): void
    {
        $this->assertSame(['exact', 'min', 'max'], Payment::INSTALLMENT_TYPES);
    }

    public function test_max_installments_falls_back_to_the_gateway_limit(): void
    {
        $this->assertEquals(
            env('CIELO_MAX_INSTALLMENTS', 12),
            $this->payment(['max_installments' => null])->max_installments
        );

        $this->assertEquals(6, $this->payment(['max_installments' => 6])->max_installments);
    }

    /* ------------------------------------------------------------------ *
     * Expiration
     * ------------------------------------------------------------------ */

    public function test_is_expired_only_after_the_expiration_date(): void
    {
        $this->assertFalse($this->payment(['expire_at' => null])->isExpired(), 'no expiration date');
        $this->assertFalse($this->payment(['expire_at' => now()->addDay()])->isExpired(), 'future');
        $this->assertTrue($this->payment(['expire_at' => now()->subDay()])->isExpired(), 'past');
    }

    public function test_status_of_an_active_payment_past_its_date_is_expired(): void
    {
        $payment = $this->payment([
            'status' => Payment::STATUS_ACTIVE,
            'expire_at' => now()->subDay(),
        ]);

        $this->assertSame(Payment::STATUS_EXPIRED, $payment->status);
        $this->assertSame(Payment::STATUS_ACTIVE, $payment->getRawOriginal('status'), 'the stored status is kept');
    }

    public function test_status_of_other_payments_is_not_changed_by_the_date(): void
    {
        $expiredDate = now()->subDay();

        $this->assertSame(
            Payment::STATUS_ACTIVE,
            $this->payment(['status' => Payment::STATUS_ACTIVE, 'expire_at' => now()->addDay()])->status
        );
        $this->assertSame(
            Payment::STATUS_PAID,
            $this->payment(['status' => Payment::STATUS_PAID, 'expire_at' => $expiredDate])->status
        );
        $this->assertSame(
            Payment::STATUS_CANCELLED,
            $this->payment(['status' => Payment::STATUS_CANCELLED, 'expire_at' => $expiredDate])->status
        );
        $this->assertSame(
            Payment::STATUS_INACTIVE,
            $this->payment(['status' => Payment::STATUS_INACTIVE, 'expire_at' => $expiredDate])->status
        );
    }

    /* ------------------------------------------------------------------ *
     * Relations
     * ------------------------------------------------------------------ */

    public function test_customer_creator_and_company_relations(): void
    {
        $company = $this->company();
        $creator = $this->user(['company_id' => $company->id]);
        $customer = $this->customer(['company_id' => $company->id]);

        $payment = $this->payment([
            'company_id' => $company->id,
            'created_by' => $creator->id,
        ]);

        // customer_id is not mass assignable, the controllers assign it
        $payment->customer_id = $customer->id;
        $payment->save();

        $this->assertSame($creator->id, $payment->creator->id);
        $this->assertSame($customer->id, $payment->customer->id);
        $this->assertSame($company->id, $payment->company->id);
    }

    public function test_gateways_are_synced_through_the_pivot(): void
    {
        $payment = $this->payment();
        $gateway = $this->gateway();

        $payment->gateways()->sync([$gateway->id]);

        $this->assertSame([$gateway->id], $payment->fresh()->gateways->pluck('id')->all());
        $this->assertDatabaseHas('payment_gateways', [
            'payment_id' => $payment->id,
            'gateway_id' => $gateway->id,
        ]);
    }

    public function test_latest_payment_is_the_last_successful_operation(): void
    {
        $payment = $this->payment();

        $this->assertNull($payment->latestPayment);

        $this->gatewayOperation($payment, GatewayOperation::PAY_OPERATION, false);
        $this->assertNull($payment->latestPayment, 'failed operations are ignored');

        $paid = $this->gatewayOperation($payment, GatewayOperation::PAY_OPERATION, true, ['paymentId' => 'abc']);
        $this->assertSame($paid->id, $payment->latestPayment->id);

        $void = $this->gatewayOperation($payment, GatewayOperation::VOID_OPERATION, true);
        $this->assertSame($void->id, $payment->latestPayment->id, 'the newest one wins');
        $this->assertSame($payment->id, $void->payment->id);
    }

    /* ------------------------------------------------------------------ *
     * Company filter
     * ------------------------------------------------------------------ */

    public function test_visible_to_keeps_only_the_payments_of_the_company(): void
    {
        $acme = $this->company();
        $other = $this->company();

        $user = $this->user(['company_id' => $acme->id]);
        $mine = $this->payment(['company_id' => $acme->id]);
        $this->payment(['company_id' => $other->id]);

        $this->assertSame([$mine->id], Payment::visibleTo($user)->pluck('id')->all());
        $this->assertSame(2, Payment::visibleTo($this->superAdmin())->count());
        $this->assertSame(2, Payment::visibleTo($this->mainUser($acme))->count());
    }

    public function test_is_visible_to(): void
    {
        $acme = $this->company();
        $user = $this->user(['company_id' => $acme->id]);

        $this->assertTrue($this->payment(['company_id' => $acme->id])->isVisibleTo($user));
        $this->assertFalse($this->payment(['company_id' => $this->company()->id])->isVisibleTo($user));
        $this->assertFalse($this->payment()->isVisibleTo($user), 'payment without company');
        $this->assertTrue($this->payment()->isVisibleTo($this->user(['company_id' => null])));
    }
}
