<?php

namespace App\Http\Requests\AttendanceAdjustmentRequest;

use App\Models\AttendanceAdjustmentRequest;
use Illuminate\Foundation\Http\FormRequest;

class ViewAttendanceAdjustmentRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $attendanceAdjustment = AttendanceAdjustmentRequest::query()->where('number', $this->route('requestNumber'))->firstOrFail();

        return $attendanceAdjustment instanceof AttendanceAdjustmentRequest;
    }
}
