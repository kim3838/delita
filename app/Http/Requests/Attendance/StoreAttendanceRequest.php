<?php

namespace App\Http\Requests\Attendance;

class StoreAttendanceRequest extends BaseAttendanceRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'employee_id' => 'required|numeric|integer',
            'shift_id' => 'required|numeric|integer',
            'date' => [
                'required',
                'date_format:Y-m-d',
            ],
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'employee_id.required' => 'Employee account is required',
            'shift_id.required' => 'Employee shift is required',
            'date.required' => 'Date is required',
            'date.date_format' => 'Date must match the format Y-m-d e.g.(2000-12-31)',
        ]);
    }
}
