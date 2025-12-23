<?php

namespace App\Http\Requests\LeaveDateRangeFilter;

use Illuminate\Foundation\Http\FormRequest;

class LeaveDateRangeFilterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric|exists:companies,id',
            'employee_id' => 'required|numeric|exists:employees,id',
            'shift_id' => 'required|numeric|exists:shifts,id',
            'leave_type_id' => 'required|numeric|exists:leave_types,id',
            'date_from' => 'required|date|date_format:Y-m-d',
            'date_to' => 'required|date|after_or_equal:date_from|date_format:Y-m-d',
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
            'date_from.date_format' => 'Date from must match the format Y-m-d e.g.(2000-12-31)',
            'date_to.date_format' => 'Date to must match the format Y-m-d e.g.(2000-12-31)',
            'date_to.after_or_equal' => 'Date to must be after or equal to date from',
        ];
    }
}
