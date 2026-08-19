<?php

namespace App\Http\Requests\Companies;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:1', 'max:255', Rule::unique('companies', 'name')->ignore($this->route('company'))],
            'status' => 'required|in:0,1',
            'main_user' => 'nullable|exists:users,id',
            'slug' => ['required', 'string', 'max:63', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/', Rule::notIn(Company::RESERVED_SLUGS), Rule::unique('companies', 'slug')->ignore($this->route('company'))],
            'site_title' => 'nullable|string|max:255',
            'logo_url' => 'nullable|string|max:255',
        ];
    }
}
