<?php

namespace App\Http\Requests\LeaveBalanceMap;

use Illuminate\Foundation\Http\FormRequest;

class LeaveBalancePeriodSeriesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric|exists:companies,id',
            'employee_id' => 'required|numeric|exists:employees,id',
            'leave_type_id' => 'required|numeric|exists:leave_types,id',
            'up_to_date' => 'required|date|date_format:Y-m-d',
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
            'up_to_date.required' => 'Date is required',
            'up_to_date.date' => 'Date must be a valid date',
            'up_to_date.date_format' => 'Date must match the format Y-m-d e.g.(2000-12-31)',
        ];
    }
}
