<?php

namespace App\Http\Requests\IncomeTax;

use App\Http\Requests\BaseEmployeePayrollComponentRequest;
use App\Models\IncomeTax;
use Illuminate\Validation\Rule;

class UpdateIncomeTaxRequest extends BaseEmployeePayrollComponentRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $incomeTax = IncomeTax::findOrfail($this->route('incomeTaxId'));

        return $this->user()->can('update', $incomeTax);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'code' => [
                'required',
                'string',
                'regex:/^\S+$/',
                'max:255',
                Rule::unique('income_taxes')->where(function ($query) {
                    return $query->where('company_id', $this->input('company_id'))
                        ->whereNot('id', $this->route('incomeTaxId'));
                })
            ],
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'code.required' => 'Code is required',
            'code.regex' => 'Code must not contain spaces.',
            'code.unique' => 'Code has already been taken.',
        ]);
    }
}
