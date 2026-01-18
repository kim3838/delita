<?php

namespace App\Http\Requests\AttendanceAdjustmentRequest;

use App\Models\AttendanceAdjustmentRequest;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyAttendanceAdjustmentRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', AttendanceAdjustmentRequest::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'attendance_adjustment_request_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'attendance_adjustment_request_ids.required' => 'Attendance adjustment ids is required',
            'attendance_adjustment_request_ids.array' => 'Attendance adjustment ids must be an array',
        ];
    }
}
