<?php

namespace App\Http\Requests\AttendanceAdjustmentRequest;

use App\Models\AttendanceAdjustmentRequest;

class StoreAttendanceAdjustmentRequestRequest extends BaseStoreAttendanceAdjustmentRequestRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', AttendanceAdjustmentRequest::class);
    }
}
