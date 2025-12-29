<?php

namespace App\Http\Requests\EmployeeLeaveType;

use App\Enums\RegexValidation;
use App\Models\EmployeeLeaveType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $employeeLeaveType = EmployeeLeaveType::query()->findOrFail($this->route('employeeLeaveTypeId'));

        return $this->user()->can('update', $employeeLeaveType);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'override_balance_upon_eligibility' => 'required|boolean',
            'balance_upon_eligibility' => [
                'sometimes',
                'required',
                'regex:' . RegexValidation::NUMERIC_1_DECIMAL->value,
                function($attribute, $value, $fail){
                    if($this->input('balance_upon_eligibility')){

                        if((float)$value < 0 || (float)$value > 999999.9){
                            $fail('Custom balance is invalid');
                        }
                    }
                }
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'override_balance_upon_eligibility.required' => 'Override balance upon eligibility is required',
            'balance_upon_eligibility.required' => 'Custom balance is required',
            'balance_upon_eligibility.regex' => 'Custom balance is invalid',
        ];
    }
}
