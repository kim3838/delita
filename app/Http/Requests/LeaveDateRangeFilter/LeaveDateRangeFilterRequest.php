<?php

namespace App\Http\Requests\LeaveDateRangeFilter;

use App\Http\Requests\LeaveDateRangeInquire\BaseLeaveDateRangeRequest;

class LeaveDateRangeFilterRequest extends BaseLeaveDateRangeRequest
{
    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric|exists:companies,id',
            'employee_id' => 'required|numeric|exists:employees,id',
            'shift_id' => 'required|numeric|exists:shifts,id',
            'leave_type_id' => 'required|numeric|exists:leave_types,id',
            ...(parent::rules())
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.exists' => 'Company not found',
            'company_id.required' => 'Company is required',
            'company_id.numeric' => 'Company id must be numeric',
            'employee_id.exists' => 'Employee not found',
            'employee_id.required' => 'Employee is required',
            'employee_id.numeric' => 'Employee id must be numeric',
            'leave_type_id.exists' => 'Leave type not found',
            'leave_type_id.required' => 'Leave type is required',
            'leave_type_id.numeric' => 'Leave type id must be numeric',
            ...(parent::messages())
        ];
    }
}
