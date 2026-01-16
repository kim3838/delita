<?php

namespace App\Listeners;

use App\Events\Repositories\AttendanceAdjustmentRequestCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Queue\InteractsWithQueue;

class AttendanceAdjustmentRequestCreatedChain
{
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
        $companyApprovalSettings = $event->attendanceAdjustmentRequest->company->approvalSettings;

        $modelAlias = Relation::getMorphAlias($event->attendanceAdjustmentRequest::class);

        $approversArray = $companyApprovalSettings
            ->where('request_model', $modelAlias)
            ->first()?->approvers
            ->map(function($approver){
                return [
                    'order' => $approver->order,
                    'approver_id' => $approver->approver_id
                ];
            })
            ->sortBy('order')
            ->values()
            ->toArray();

        //Create approval states
        $event->attendanceAdjustmentRequest->approvalStates()->createMany($approversArray);
    }
}
