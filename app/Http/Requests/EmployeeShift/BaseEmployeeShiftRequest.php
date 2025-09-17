<?php

namespace App\Http\Requests\EmployeeShift;

use Illuminate\Foundation\Http\FormRequest;

class BaseEmployeeShiftRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'start_date' => 'required|date_format:Y-m-d',
            'stated_shift_end_date' => 'required|boolean',
            'end_date' => 'sometimes|required|date_format:Y-m-d|after_or_equal:start_date',
            'employees' => 'required|array',
            'shifts' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'employees.required' => 'Employees is required',
            'shifts.required' => 'Shifts is required',
            'start_date.required' => 'Shift start date is required',
            'end_date.required' => 'Shift end date is required if stated shift end date is true',
            'stated_shift_end_date.required' => 'Stated shift end date is required',
            'employees.array' => 'Employees must be an array',
            'shifts.array' => 'Shifts must be an array',
            'start_date.date_format' => 'Start date must match the format Y-m-d (2000-01-01)',
            'end_date.date_format' => 'End date must match the format Y-m-d (2000-01-01)',
            'stated_shift_end_date.boolean' => 'Stated shift end date must be a boolean',
            'end_date.after_or_equal' => 'End date must be equal to or after the start date',
        ];
    }
}
