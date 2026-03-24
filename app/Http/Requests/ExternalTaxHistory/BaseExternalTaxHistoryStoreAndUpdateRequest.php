<?php

namespace App\Http\Requests\ExternalTaxHistory;

use App\Enums\RegexValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BaseExternalTaxHistoryStoreAndUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'employee_id' => 'required|numeric|exists:employees,id',
            'year' => [
                'required',
                'numeric',
                'digits:4',
                Rule::unique('external_tax_histories')
                    ->ignore($this->route('externalTaxHistoryUlid'), 'ulid')
                    ->where(function ($query) {
                        return $query->where('employee_id', $this->input('employee_id'));
                    })
            ],
            'total_taxable' => [
                'required',
                'numeric',
                'min:0',
                'regex:' . RegexValidation::NUMERIC_12_DIGITS_2_DECIMALS->value,
            ],
            'total_nontaxable_bonus' => [
                'required',
                'numeric',
                'min:0',
                'regex:' . RegexValidation::NUMERIC_12_DIGITS_2_DECIMALS->value,
            ],
            'total_taxable_from_bonus' => [
                'required',
                'numeric',
                'min:0',
                'regex:' . RegexValidation::NUMERIC_12_DIGITS_2_DECIMALS->value,
            ],
            'total_tax_withheld' => [
                'required',
                'numeric',
                'min:0',
                'regex:' . RegexValidation::NUMERIC_12_DIGITS_2_DECIMALS->value,
            ],
            'remarks' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.exists' => 'Employee not found',
            'employee_id.required' => 'Employee is required',
            'employee_id.numeric' => 'Employee id must be numeric',
            'year.required' => 'Year is required',
            'year.numeric' => 'Year must be numeric',
            'year.digits' => 'Year must be exactly 4 digits',
            'year.unique' => 'Year already exists',

            'total_taxable.required' => 'Total taxable is required',
            'total_taxable.numeric' => 'Total taxable must be numeric',
            'total_taxable.min' => 'Total taxable must be at least 0',
            'total_taxable.regex' => 'Total taxable must be a valid number with up to 12 digits and maximum 2 decimal places',

            'total_nontaxable_bonus.required' => 'Total nontaxable bonus is required',
            'total_nontaxable_bonus.numeric' => 'Total nontaxable bonus must be numeric',
            'total_nontaxable_bonus.min' => 'Total nontaxable bonus must be at least 0',
            'total_nontaxable_bonus.regex' => 'Total nontaxable bonus must be a valid number with up to 12 digits and maximum 2 decimal places',

            'total_taxable_from_bonus.required' => 'Total taxable from bonus is required',
            'total_taxable_from_bonus.numeric' => 'Total taxable from bonus must be numeric',
            'total_taxable_from_bonus.min' => 'Total taxable from bonus must be at least 0',
            'total_taxable_from_bonus.regex' => 'Total taxable from bonus must be a valid number with up to 12 digits and maximum 2 decimal places',

            'total_tax_withheld.required' => 'Total tax withheld is required',
            'total_tax_withheld.numeric' => 'Total tax withheld must be numeric',
            'total_tax_withheld.min' => 'Total tax withheld must be at least 0',
            'total_tax_withheld.regex' => 'Total tax withheld must be a valid number with up to 12 digits and maximum 2 decimal places',

            'remarks.string' => 'Remarks must be a string',
            'remarks.max' => 'Remarks must not be greater than 255 characters',
        ];
    }
}
