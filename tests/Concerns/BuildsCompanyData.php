<?php

namespace Tests\Concerns;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Gateway;
use App\Models\GatewayOperation;
use App\Models\Payment;
use App\Models\User;

trait BuildsCompanyData
{
    protected function company(array $attributes = []): Company
    {
        static $sequence = 0;
        $sequence++;

        return Company::create(array_merge([
            'name' => 'Empresa ' . $sequence,
            'slug' => 'empresa-' . $sequence,
            'status' => true,
        ], $attributes));
    }

    protected function user(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    /**
     * User pointed by config('company.super_admin_id').
     */
    protected function superAdmin(array $attributes = []): User
    {
        $user = $this->user(array_merge(['is_admin' => true], $attributes));
        config(['company.super_admin_id' => $user->id]);

        return $user;
    }

    /**
     * User set as the main user of the given company.
     */
    protected function mainUser(Company $company, array $attributes = []): User
    {
        $user = $this->user(array_merge(['is_admin' => true, 'company_id' => $company->id], $attributes));
        $company->update(['main_user' => $user->id]);

        return $user->fresh();
    }

    protected function payment(array $attributes = []): Payment
    {
        return Payment::create(array_merge([
            'description' => 'Pagamento de teste',
            'value' => 100.00,
            'status' => Payment::STATUS_ACTIVE,
        ], $attributes));
    }

    protected function customer(array $attributes = []): Customer
    {
        static $sequence = 0;
        $sequence++;

        return Customer::create(array_merge([
            'name' => 'Cliente ' . $sequence,
            'email' => 'cliente' . $sequence . '@example.com',
            'cpf' => '1111111111' . $sequence,
        ], $attributes));
    }

    protected function gateway(array $attributes = []): Gateway
    {
        return Gateway::factory()->create(array_merge(['name' => 'CIELO API 3.0', 'status' => true], $attributes));
    }

    protected function gatewayOperation(Payment $payment, string $type, bool $status = true, array $log = []): GatewayOperation
    {
        $operation = new GatewayOperation();
        $operation->payment_id = $payment->id;
        $operation->gateway_id = (Gateway::first() ?? $this->gateway())->id;
        $operation->type = $type;
        $operation->status = $status;
        $operation->log = json_encode($log);
        $operation->save();

        return $operation;
    }
}
