<?php

namespace App\Http\Requests\Company;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Company::class);
    }

    public function rules(): array
    {
        return [
            'account_id' => 'required|numeric',
            'country_id' => 'required|numeric',
            'currency' => 'required|string',
            'code' => 'required|string|max:255|unique:companies,code',
            'name' => 'required|string|max:255',
            'timezone' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'account_id.required' => 'Account number is required',
            'code.required' => 'Company code is required',
            'country_id.required' => 'Country is required',
            'currency.required' => 'Currency is required',
            'code.unique' => 'Code has already been taken',
            'code.max' => 'Code must not be greater than 255 characters',
            'name.required' => 'Company name is required',
            'name.max' => 'Company name must not be greater than 255 characters',
            'timezone.required' => 'Company timezone is required',
        ];
    }
}
