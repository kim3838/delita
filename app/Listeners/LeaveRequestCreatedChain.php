<?php

namespace App\Listeners;

use App\Events\Repositories\LeaveRequestCreated;
use App\Traits\HasApproval;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Queue\InteractsWithQueue;

class LeaveRequestCreatedChain
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
    public function handle(LeaveRequestCreated $event): void
    {
        $leaveRequest = $event->leaveRequest;
        $modelThroughEmployeeForeign = $leaveRequest->employee_id;
        $requestedBy = $leaveRequest->requestedBy;

        $modelAlias = Relation::getMorphAlias($event->leaveRequest::class);

        $approversArray = $this->getRequestableApprovers($modelAlias, $modelThroughEmployeeForeign, $event->leaveRequest->company, $requestedBy->id);

        //Create approval states
        $event->leaveRequest->approvalStates()->createMany($approversArray);
    }
}
