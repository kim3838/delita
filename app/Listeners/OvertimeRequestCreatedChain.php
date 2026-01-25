<?php

namespace App\Listeners;

use App\Events\Repositories\OvertimeRequestCreated;
use App\Traits\HasApproval;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Queue\InteractsWithQueue;

class OvertimeRequestCreatedChain
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
    public function handle(OvertimeRequestCreated $event): void
    {
        $overtimeRequest = $event->overtimeRequest;
        $modelThroughEmployeeForeign = $overtimeRequest->attendance_id;
        $requestedBy = $overtimeRequest->requestedBy;

        $modelAlias = Relation::getMorphAlias($event->overtimeRequest::class);

        $approversArray = $this->getRequestableApprovers($modelAlias, $modelThroughEmployeeForeign, $event->overtimeRequest->company, $requestedBy->id);

        //Create approval states
        $event->overtimeRequest->approvalStates()->createMany($approversArray);
    }
}
