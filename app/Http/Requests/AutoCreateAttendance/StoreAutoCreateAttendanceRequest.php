<?php

namespace App\Http\Requests\AutoCreateAttendance;

use App\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;

class StoreAutoCreateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Attendance::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric|exists:companies,id',
            'employee_ids' => 'nullable|array',
            'assigned_employee_group_ids' => 'nullable|array',
            'replace_existing_attendance' => 'boolean',
            'date_from' => 'required|date|date_format:Y-m-d',
            'date_to' => 'required|date|after_or_equal:date_from|date_format:Y-m-d',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $employees = $this->input('employee_ids', []);
            $groups = $this->input('assigned_employee_group_ids', []);

            if (empty($employees) && empty($groups)) {
                $validator->errors()->add(
                    'employee_ids',
                    'Either employee/s or employee group/s must have at least one value.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'company_id.exists' => 'Company not found',
            'company_id.required' => 'Company is required',
            'company_id.numeric' => 'Company id must be numeric',
            'date_from.date_format' => 'Date from must match the format Y-m-d e.g.(2000-12-31)',
            'date_to.date_format' => 'Date to must match the format Y-m-d e.g.(2000-12-31)',
            'date_to.after_or_equal' => 'Date to must be after or equal to date from',
        ];
    }
}
