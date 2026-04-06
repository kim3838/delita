<?php

namespace App\Listeners;

use App\Concrete\ApprovalService;
use App\Concrete\AwaitingApprovalContext;
use App\Events\Repositories\PayrollRequestCreated;
use App\Traits\HasApproval;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Queue\InteractsWithQueue;

class PayrollRequestCreatedChain
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
    public function handle(PayrollRequestCreated $event): void
    {
        $payrollRequest = $event->payrollRequest;

        $modelAlias = Relation::getMorphAlias($event->payrollRequest::class);

        $approversArray = $this->getRequestableApprovers($modelAlias, null, $event->payrollRequest->company);

        //Create approval states
        $event->payrollRequest->approvalStates()->createMany($approversArray);

        /**
         * Notify first approver
         **/
        $approvalService = new ApprovalService();

        $awaitingApprovalContext = new AwaitingApprovalContext($modelAlias, $payrollRequest->id,);
        $awaitingApprovalContext->requestable = $payrollRequest;

        $approvalService->initializeNextAwaitingApproverNotification($awaitingApprovalContext, $modelAlias);
    }
}
