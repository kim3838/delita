<?php

namespace App\Http\Requests\IncomeTax;

use App\Http\Requests\BaseEmployeePayrollComponentRequest;
use App\Models\IncomeTax;
use Illuminate\Validation\Rule;

class StoreIncomeTaxRequest extends BaseEmployeePayrollComponentRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', IncomeTax::class);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('income_taxes')->where(function ($query) {
                    return $query->where('company_id', $this->input('company_id'));
                })
            ],

        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'code.required' => 'Code is required',
            'code.unique' => 'Code has already been taken.',
        ]);
    }
}
