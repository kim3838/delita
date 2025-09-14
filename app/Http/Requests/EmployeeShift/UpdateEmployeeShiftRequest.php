<?php

namespace App\Http\Requests\EmployeeShift;

use App\Models\EmployeeShift;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $employeeShift = EmployeeShift::query()->findOrFail($this->route('employeeShiftId'));

        return $this->user()->can('update', $employeeShift);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'start_date' => 'required|date_format:Y-m-d',
            'stated_shift_end_date' => 'required|boolean',
            'end_date' => 'sometimes|required|date_format:Y-m-d',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'start_date.required' => 'Shift start date is required',
            'end_date.required' => 'Shift end date is required if stated shift end date is true',
            'stated_shift_end_date.required' => 'Stated shift end date is required',
            'start_date.date_format' => 'Start date must match the format Y-m-d (2000-01-01)',
            'end_date.date_format' => 'End date must match the format Y-m-d (2000-01-01)',
            'stated_shift_end_date.boolean' => 'Stated shift end date must be a boolean',
        ];
    }
}
