<?php

namespace App\Http\Requests\Company;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $company = Company::query()->findOrfail($this->route('companyId'));

        return $this->user()->can('update', $company);
    }

    public function rules(): array
    {
        return [
            'account_id' => 'required|numeric',
            'country_id' => 'required|numeric',
            'currency' => 'required|string',
            'code' => [
                'required',
                'string',
                'regex:/^\S+$/',
                'max:255',
                Rule::unique('companies')->ignore($this->route('companyId'))
            ],
            'short_name' => 'required|string|max:25',
            'name' => 'required|string|max:255',
            'timezone' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'account_id.required' => 'Account number is required',
            'code.required' => 'Company code is required',
            'code.regex' => 'Code must not contain spaces',
            'country_id.required' => 'Country is required',
            'currency.required' => 'Currency is required',
            'code.unique' => 'Code has already been taken',
            'code.max' => 'Code must not be greater than 255 characters',
            'short_name.required' => 'Company short name is required',
            'short_name.max' => 'Company short name must not be greater than 25 characters',
            'name.required' => 'Company name is required',
            'name.max' => 'Company name must not be greater than 255 characters',
            'timezone.required' => 'Company timezone is required',
        ];
    }
}
