<?php

namespace App\Http\Requests\Deduction;

use App\Enums\RegexValidation;
use App\Http\Requests\PayrollComponent\BasePayrollComponentRequest;
use App\Models\Deduction;
use Illuminate\Validation\Rule;

class StoreDeductionRequest extends BasePayrollComponentRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Deduction::class);
    }

    public function rules(): array
    {
        return array_merge([
            'code' => [
                'required',
                'string',
                'regex:' . RegexValidation::NO_WHITESPACE->value,
                'max:255',
                Rule::unique('deductions')->where(function ($query) {
                    return $query->where('company_id', $this->input('company_id'));
                })
            ],
        ], parent::rules());
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'code.required' => 'Code is required',
            'code.regex' => 'Code must not contain spaces',
            'code.unique' => 'Code has already been taken',
        ]);
    }
}
