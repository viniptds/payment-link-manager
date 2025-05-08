<?php

namespace App\Http\Requests\PublicPayment;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PersonalDataRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'name' => 'required',
            'cpf' => 'required|cpf|unique:customers,cpf',
            // 'document' => 'required|numeric',
            'customer_id' => 'nullable|exists:customers,id'
        ];
    }
}
