<?php

namespace App\Http\Requests\EmployeeLeaveType;

use Illuminate\Foundation\Http\FormRequest;

class BaseEmployeeLeaveTypeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'override_balance_upon_eligibility' => 'required|boolean',
            'balance_upon_eligibility' => 'sometimes|required|integer|min:0|max:9999',
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
            'balance_upon_eligibility.integer' => 'Custom balance must be an integer',
            'balance_upon_eligibility.min' => 'Custom balance must be greater than or equal to 0',
            'balance_upon_eligibility.max' => 'Custom balance must be less than 10000',

            'employees.required' => 'Employees is required',
            'employees.array' => 'Employees must be an array',
            'leave_types.array' => 'Leave types must be an array',
            'leave_types.required' => 'Leave types is required',
        ];
    }
}
