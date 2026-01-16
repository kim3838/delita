<?php

namespace App\Observers;

use App\Events\Repositories\AttendanceAdjustmentRequestCreated;
use App\Models\AttendanceAdjustmentRequest;

class AttendanceAdjustmentRequestObserver
{
    public function created(AttendanceAdjustmentRequest $attendanceAdjustmentRequest): void
    {
        event(new AttendanceAdjustmentRequestCreated($attendanceAdjustmentRequest));
    }
}
