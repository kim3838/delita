<?php

namespace App\Observers;

use App\Events\Repositories\LeaveRequestCreated;
use App\Models\LeaveRequest;
use App\Models\RequestApprovalState;
use Carbon\Carbon;

class LeaveRequestObserver
{
    public function creating(LeaveRequest $leaveRequest): bool
    {
        $this->addCustomNumberAttribute($leaveRequest);

        return true;
    }

    public function created(LeaveRequest $leaveRequest): void
    {
        event(new LeaveRequestCreated($leaveRequest));
    }

    public function deleting(LeaveRequest $leaveRequest): true
    {
        $approvalStates = $leaveRequest->approvalStates;

        $approvalStates->each(function (RequestApprovalState $approvalState) {

            $approvalState->delete();
        });

        return true;
    }

    public function addCustomNumberAttribute(LeaveRequest $leaveRequest): LeaveRequest
    {
        $series = 1;
        $seriesLength = 4;

        $dateRequested = Carbon::parse($leaveRequest->date_requested);
        $companyId = $leaveRequest->company_id;
        $employee = $leaveRequest->employee;
        $employeeNumber = $employee?->number;

        $seriesUpToDate = LeaveRequest::query()
            ->leftJoin('employees', 'leave_requests.employee_id', '=', 'employees.id')
            ->where('leave_requests.company_id', $companyId)
            ->when(!empty($employee->id), function ($builder) use ($employee) {
                $builder->where('employees.id', $employee->id);
            })
            ->whereBetween('date_requested', [
                Carbon::parse($dateRequested)->startOfYear()->toDateTimeString(),
                Carbon::parse($dateRequested)->endOfYear()->toDateTimeString()
            ])
            ->selectRaw("MAX(RIGHT(leave_requests.number, " . $seriesLength . ")) as max_series")
            ->value('max_series');

        $series = $series + $seriesUpToDate;

        $yearCreating = $dateRequested->year;
        $series = str_pad($series,$seriesLength, '0',STR_PAD_LEFT);
        $prefix = 'LR' . '-' . (!empty($employeeNumber) ? $employeeNumber : '');

        $number = "{$prefix}{$yearCreating}{$series}";

        $leaveRequest->number = $number;

        return $leaveRequest;
    }
}
