<?php

namespace App\Observers;

use App\Events\Repositories\OvertimeRequestCreated;
use App\Models\OvertimeRequest;
use App\Models\RequestApprovalState;
use Carbon\Carbon;

class OvertimeRequestObserver
{
    public function creating(OvertimeRequest $overtimeRequest): bool
    {
        $this->addCustomNumberAttribute($overtimeRequest);

        return true;
    }

    public function created(OvertimeRequest $overtimeRequest): void
    {
        event(new OvertimeRequestCreated($overtimeRequest));
    }

    public function deleting(OvertimeRequest $overtimeRequest): true
    {
        $approvalStates = $overtimeRequest->approvalStates;

        $approvalStates->each(function (RequestApprovalState $approvalState) {

            $approvalState->delete();
        });

        return true;
    }

    public function addCustomNumberAttribute(OvertimeRequest $overtimeRequest): OvertimeRequest
    {
        $series = 1;
        $seriesLength = 4;

        $dateRequested = Carbon::parse($overtimeRequest->date_requested);
        $companyId = $overtimeRequest->company_id;
        $employee = $overtimeRequest->attendance?->employee;
        $employeeNumber = $employee?->number;

        $seriesUpToDate = OvertimeRequest::query()
            ->leftJoin('attendances', 'overtime_requests.attendance_id', '=', 'attendances.id')
            ->leftJoin('employees', 'attendances.employee_id', '=', 'employees.id')
            ->where('overtime_requests.company_id', $companyId)
            ->when(!empty($employee->id), function ($builder) use ($employee) {
                $builder->where('employees.id', $employee->id);
            })
            ->whereBetween('date_requested', [
                Carbon::parse($dateRequested)->startOfYear()->toDateTimeString(),
                Carbon::parse($dateRequested)->endOfYear()->toDateTimeString()
            ])
            ->selectRaw("MAX(RIGHT(overtime_requests.number, " . $seriesLength . ")) as max_series")
            ->value('max_series');

        $series = $series + $seriesUpToDate;

        $yearCreating = $dateRequested->year;
        $series = str_pad($series,$seriesLength, '0',STR_PAD_LEFT);
        $prefix = 'OTR' . '-' . (!empty($employeeNumber) ? $employeeNumber : '');

        $number = "{$prefix}{$yearCreating}{$series}";

        $overtimeRequest->number = $number;

        return $overtimeRequest;
    }
}
