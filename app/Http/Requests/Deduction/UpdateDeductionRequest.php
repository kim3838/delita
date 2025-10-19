<?php

namespace App\Http\Requests\Deduction;

use App\Http\Requests\BaseEmployeePayrollComponentRequest;
use App\Models\Deduction;
use Illuminate\Validation\Rule;

class UpdateDeductionRequest extends BaseEmployeePayrollComponentRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $deduction = Deduction::findOrfail($this->route('deductionId'));

        return $this->user()->can('update', $deduction);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'code' => [
                'required',
                'string',
                'regex:/^\S+$/',
                'max:255',
                Rule::unique('deductions')->where(function ($query) {
                    return $query->where('company_id', $this->input('company_id'))
                        ->whereNot('id', $this->route('deductionId'));
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
