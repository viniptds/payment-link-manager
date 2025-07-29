<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // 'value' => 'required|numeric|decimal:0,2|gte:' . env('CIELO_MIN_INSTALLMENT_VALUE'),
            'value' => 'required|numeric|decimal:0,2|gt:0',
            'description' => 'required|string|min:1|max:100',
            'expire_at' => 'nullable|date|after_or_equal:now',
            'max_installments' => 'nullable|numeric|gt:0|lte:' . env('CIELO_MAX_INSTALLMENTS', 12),
            'gateway_ids' => 'nullable|array',
            'gateway_ids.*' => 'nullable|exists:gateways,id',
            'customer_id' => 'nullable|exists:customers'
        ];
    }
}
