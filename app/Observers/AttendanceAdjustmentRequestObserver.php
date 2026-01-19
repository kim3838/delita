<?php

namespace App\Observers;

use App\Events\Repositories\AttendanceAdjustmentRequestCreated;
use App\Models\AttendanceAdjustmentRequest;
use App\Models\RequestApprovalState;
use Carbon\Carbon;

class AttendanceAdjustmentRequestObserver
{
    public function creating(AttendanceAdjustmentRequest $attendanceAdjustmentRequest): bool
    {
        $this->addCustomNumberAttribute($attendanceAdjustmentRequest);

        return true;
    }

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

    public function addCustomNumberAttribute(AttendanceAdjustmentRequest $attendanceAdjustmentRequest): AttendanceAdjustmentRequest
    {
        $series = 1;

        $dateRequested = Carbon::parse($attendanceAdjustmentRequest->date_requested);
        $companyId = $attendanceAdjustmentRequest->company_id;
        $employee = $attendanceAdjustmentRequest->attendance?->employee;
        $employeeNumber = $employee?->number;

        $seriesUpToDate = AttendanceAdjustmentRequest::query()
            ->leftJoin('attendances', 'attendance_adjustment_requests.attendance_id', '=', 'attendances.id')
            ->leftJoin('employees', 'attendances.employee_id', '=', 'employees.id')
            ->where('attendance_adjustment_requests.company_id', $companyId)
            ->when(!empty($employee->id), function ($builder) use ($employee) {
                $builder->where('employees.id', $employee->id);
            })
            ->whereBetween('date_requested', [
                Carbon::parse($dateRequested)->startOfYear()->toDateTimeString(),
                Carbon::parse($dateRequested)->endOfYear()->toDateTimeString()
            ])->count();

        $series = $series + $seriesUpToDate;

        $yearCreating = $dateRequested->year;
        $series = str_pad($series,3, '0',STR_PAD_LEFT);
        $prefix = 'AA' . '-' . (!empty($employeeNumber) ? $employeeNumber : '');

        $number = "{$prefix}{$yearCreating}{$series}";

        $attendanceAdjustmentRequest->number = $number;

        return $attendanceAdjustmentRequest;
    }
}
