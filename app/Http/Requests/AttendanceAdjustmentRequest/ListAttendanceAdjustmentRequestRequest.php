<?php

namespace App\Http\Requests\AttendanceAdjustmentRequest;

use App\Models\AttendanceAdjustmentRequest;
use Illuminate\Foundation\Http\FormRequest;

class ListAttendanceAdjustmentRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', AttendanceAdjustmentRequest::class);
    }
}
