<?php

namespace App\Http\Requests\Gateways;

use Illuminate\Foundation\Http\FormRequest;

class StoreGatewayRequest extends FormRequest
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
            'name' => 'required|string|unique:gateways,name',
            'description' => 'required|string',
            'photo' => 'nullable',
            'credentials.*' => 'nullable|array',
            'credentials.label' => 'nullable|array',
            'credentials.label.*' => 'nullable|string',
            'credentials.value' => 'nullable|array',
            'credentials.value.*' => 'nullable|string',
        ];
    }
}
