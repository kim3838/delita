<?php

namespace App\Listeners;

use App\Events\Repositories\AttendanceAdjustmentRequestCreated;
use App\Traits\HasApproval;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Queue\InteractsWithQueue;

class AttendanceAdjustmentRequestCreatedChain
{
    use HasApproval;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AttendanceAdjustmentRequestCreated $event): void
    {
        $attendanceAdjustmentRequest = $event->attendanceAdjustmentRequest;
        $modelThroughEmployeeForeign = $attendanceAdjustmentRequest->attendance_id;
        $requestedBy = $attendanceAdjustmentRequest->requestedBy;

        $modelAlias = Relation::getMorphAlias($event->attendanceAdjustmentRequest::class);

        $approversArray = $this->getRequestableApprovers($modelAlias, $modelThroughEmployeeForeign, $event->attendanceAdjustmentRequest->company, $requestedBy->id);

        //Create approval states
        $event->attendanceAdjustmentRequest->approvalStates()->createMany($approversArray);
    }
}
