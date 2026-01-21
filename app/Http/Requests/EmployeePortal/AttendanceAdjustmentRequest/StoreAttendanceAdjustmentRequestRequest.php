<?php

namespace App\Http\Requests\EmployeePortal\AttendanceAdjustmentRequest;

use App\Http\Requests\AttendanceAdjustmentRequest\StoreAttendanceAdjustmentRequestRequest as StoreAttendanceAdjustmentRequestRequestAlias;

class StoreAttendanceAdjustmentRequestRequest extends StoreAttendanceAdjustmentRequestRequestAlias
{
    public function authorize(): bool
    {
        return true;
    }
}
