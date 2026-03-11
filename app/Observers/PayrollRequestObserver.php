<?php

namespace App\Observers;

use App\Events\Repositories\PayrollRequestCreated;
use App\Models\PayrollRequest;
use App\Models\RequestApprovalState;
use Carbon\Carbon;

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
        $series = 1;
        $seriesLength = 4;
        $dateRequested = Carbon::parse($payrollRequest->date_requested);

        $seriesUpToDate = PayrollRequest::query()
            ->whereBetween('date_requested', [
                Carbon::parse($dateRequested)->startOfYear()->toDateTimeString(),
                Carbon::parse($dateRequested)->endOfYear()->toDateTimeString()
            ])
            ->selectRaw("MAX(RIGHT(payroll_requests.number, " . $seriesLength . ")) as max_series")
            ->value('max_series');

        $series = $series + (int)$seriesUpToDate;
        $yearCreating = $dateRequested->year;
        $series = str_pad($series,$seriesLength, '0',STR_PAD_LEFT);

        $payrollRequest->number = "PRR-$yearCreating$series";

        return $payrollRequest;
    }
}
