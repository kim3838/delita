<?php

namespace App\Observers;

use App\Events\Repositories\PayrollRequestCreated;
use App\Models\PayrollRequest;
use App\Models\RequestApprovalState;

class PayrollRequestObserver
{
    public function creating(PayrollRequest $payrollRequest): bool
    {
        $this->addCustomNumberAttribute($payrollRequest);

        return true;
    }

    public function created(PayrollRequest $payrollRequest): void
    {
        event(new PayrollRequestCreated($payrollRequest));
    }

    public function deleting(PayrollRequest $payrollRequest): true
    {
        $approvalStates = $payrollRequest->approvalStates;

        $approvalStates->each(function (RequestApprovalState $approvalState) {

            $approvalState->delete();
        });

        return true;
    }

    public function addCustomNumberAttribute(PayrollRequest $payrollRequest): PayrollRequest
    {
        $payrollRequest->number = $payrollRequest->payroll->number;

        return $payrollRequest;
    }
}
