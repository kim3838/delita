<?php

namespace App\Http\Requests\Attendance;

use App\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', Attendance::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'attendance_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'attendance_ids.required' => 'Attendance is required',
            'attendance_ids.array' => 'Attendance must be an array',
        ];
    }
}
