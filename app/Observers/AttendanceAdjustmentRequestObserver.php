<?php

namespace App\Observers;

use App\Events\Repositories\AttendanceAdjustmentRequestCreated;
use App\Models\AttendanceAdjustmentRequest;
use App\Models\RequestApprovalState;

class AttendanceAdjustmentRequestObserver
{
    public function created(AttendanceAdjustmentRequest $attendanceAdjustmentRequest): void
    {
        event(new AttendanceAdjustmentRequestCreated($attendanceAdjustmentRequest));
    }

    public function deleting(AttendanceAdjustmentRequest $attendanceAdjustmentRequest): true
    {
        $approvalStates = $attendanceAdjustmentRequest->approvalStates;

        $approvalStates->each(function (RequestApprovalState $approvalState) {

            $approvalState->delete();
        });

        return true;
    }
}
