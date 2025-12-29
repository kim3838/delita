<?php

namespace App\Http\Requests\LeaveBalanceAdjustment;

use App\Enums\LeaveBalanceAdjustmentType;
use App\Enums\RegexValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BaseLeaveBalanceAdjustmentStoreAndUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric|exists:companies,id',
            'employee_id' => 'required|numeric|exists:employees,id',
            'leave_type_id' => 'required|numeric|exists:leave_types,id',
            'type' => [
                'required',
                'integer',
                Rule::in([
                    LeaveBalanceAdjustmentType::ADD->value,
                    LeaveBalanceAdjustmentType::DEDUCT->value,
                ])
            ],
            'effective_date' => 'required|date|date_format:Y-m-d',
            'balance' => [
                'required',
                'regex:' . RegexValidation::NUMERIC_1_DECIMAL->value,
                function($attribute, $value, $fail){

                    if((float)$value == 0.0){
                        $fail('Balance must be greater than 0');
                    }

                    if((float)$value < 0 || (float)$value > 999999.9){
                        $fail('Balance is invalid');
                    }
                }
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.exists' => 'Company not found',
            'company_id.required' => 'Company is required',
            'employee_id.exists' => 'Employee not found',
            'employee_id.required' => 'Employee is required',
            'employee_id.numeric' => 'Employee id must be numeric',
            'leave_type_id.exists' => 'Leave type not found',
            'leave_type_id.required' => 'Leave type is required',
            'leave_type_id.numeric' => 'Leave type id must be numeric',
            'effective_date.required' => 'Date is required',
            'effective_date.date' => 'Date must be a valid date',
            'effective_date.date_format' => 'Date must match the format Y-m-d e.g.(2000-12-31)',
            'balance.required' => 'Balance is required',
            'balance.regex' => 'Balance is invalid',
        ];
    }
}
