<?php

namespace App\Http\Requests\EmployeeLeaveType;

use App\Enums\RegexValidation;
use Illuminate\Foundation\Http\FormRequest;

class BaseEmployeeLeaveTypeRequest extends FormRequest
{
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
            'employees' => 'required|array',
            'leave_types' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'override_balance_upon_eligibility.required' => 'Override balance upon eligibility is required',
            'balance_upon_eligibility.required' => 'Custom balance is required',
            'balance_upon_eligibility.regex' => 'Custom balance is invalid',

            'employees.required' => 'Employees is required',
            'employees.array' => 'Employees must be an array',
            'leave_types.array' => 'Leave types must be an array',
            'leave_types.required' => 'Leave types is required',
        ];
    }
}
