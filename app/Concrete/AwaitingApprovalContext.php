<?php

namespace App\Concrete;

use App\Blueprint\Repositories\AttendanceAdjustmentRequestRepository;
use App\Blueprint\Repositories\LeaveRequestRepository;
use App\Blueprint\Repositories\OvertimeRequestRepository;
use App\Blueprint\Repositories\PayrollRequestRepository;
use App\Blueprint\RequestInterface;
use App\Facades\Fractal;
use App\Models\User;
use App\Transformers\AttendanceAdjustmentRequest\ItemTransformer as AttendanceAdjustmentRequestItemTransformer;
use App\Transformers\LeaveRequest\ItemTransformer as LeaveRequestItemTransformer;
use App\Transformers\OvertimeRequest\ItemTransformer as OvertimeRequestItemTransformer;
use App\Transformers\PayrollRequest\ItemTransformer as PayrollRequestItemTransformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AwaitingApprovalContext
{
    public string $title;

    public array $mailPayload = [];
    public array $mailPayloadSummary = [];

    public function __construct(
        public string $requestableType,
        public int $requestableId,
    ){
        $this->title = match($requestableType){
            'attendance_adjustment_request' => 'Attendance adjustment request',
            'leave_request' => 'Leave request',
            'overtime_request' => 'Overtime request',
            'payroll_request' => 'Payroll workflow',
        };
    }

    public Model $requestable {

        set(Model $value) {
            $this->requestable = $value;
        }
    }

    public User $approver {

        set(User $value) {
            $this->approver = $value;
        }
    }

    public function resolveRequestable(): void
    {
        if(empty($this->requestable)){
            $this->requestable = app($this->requestableType)->show($this->requestableId);
        }
    }

    public function setMailPayload(): void
    {
        $requestInterface = app(RequestInterface::class);

        $transformer = match($this->requestableType){
            'attendance_adjustment_request' => AttendanceAdjustmentRequestItemTransformer::class,
            'overtime_request' => OvertimeRequestItemTransformer::class,
            'leave_request' => LeaveRequestItemTransformer::class,
            'payroll_request' => PayrollRequestItemTransformer::class,
            default => null,
        };

        if(empty($transformer)) return;

        $repository = match($this->requestableType){
            'attendance_adjustment_request' => AttendanceAdjustmentRequestRepository::class,
            'overtime_request' => OvertimeRequestRepository::class,
            'leave_request' => LeaveRequestRepository::class,
            'payroll_request' => PayrollRequestRepository::class,
            default => null,
        };

        if(empty($repository)) return;

        $filters = (object)[
            'company_id' => $this->requestable->company_id,
            'request_numbers' => [$this->requestable->number],
        ];

        $requestInterface->filters = (object)[
            'company_id' => $this->requestable->company_id,
        ];

        $requestablePayload = app($repository)->showFromFilters($filters);

        $this->mailPayload = Fractal::item($requestablePayload, $transformer);
    }


    public function summarizeMailPayload(): void
    {
        $this->mailPayloadSummary = [];

        switch($this->requestableType){

            case 'attendance_adjustment_request':

                $number = Arr::get($this->mailPayload, 'number');
                $numberSubtitle = Arr::get($this->mailPayload, 'attendance.date_readable') .
                    ' ' .
                    Arr::get($this->mailPayload, 'attendance.week_day_name') .
                    ' Attendance adjustment';
                $requestedByUsername = Arr::get($this->mailPayload, 'requested_by.username');
                $dateRequestedDiff = Arr::get($this->mailPayload, 'date_requested_diff');
                $attendanceOf = '(' . Arr::get($this->mailPayload, 'attendance.employee.number') . ')' .
                    ' ' .
                    Arr::get($this->mailPayload, 'attendance.employee.full_name');

                //Schedule
                $shiftRequiresLunchInAndOut = Arr::get($this->mailPayload, 'attendance.shift.require_lunch_time_in_and_out', false);
                $shiftIsFlexible = Arr::get($this->mailPayload, 'attendance.shift_schedule.is_flexible', false);
                $shiftHasLunchBreak = Arr::get($this->mailPayload, 'attendance.shift_schedule.has_lunch_break', false);
                $attendanceShiftRequiresLunchOutAndIn = $shiftRequiresLunchInAndOut && !$shiftIsFlexible && $shiftHasLunchBreak;

                $scheduleWorkPeriod = Arr::get($this->mailPayload, 'attendance.shift_schedule.work_start') . ' - ' .
                    Arr::get($this->mailPayload, 'attendance.shift_schedule.work_end') .
                    '(' . Arr::get($this->mailPayload, 'attendance.shift_schedule.timezone') . ')';

                $scheduleLunchPeriod = Arr::get($this->mailPayload, 'attendance.shift_schedule.lunch_break_start') . ' - ' .
                    Arr::get($this->mailPayload, 'attendance.shift_schedule.lunch_break_end');
                $shiftWorkStartGrace = Arr::get($this->mailPayload, 'attendance.shift.work_start_grace_time_readable');
                $shiftLunchStartGrace = Arr::get($this->mailPayload, 'attendance.shift.lunch_start_grace_time_readable');

                $scheduleTotalDuration = Arr::get($this->mailPayload, 'attendance.shift_schedule.total_work_hours_with_breaks');
                $overtimeMaxDuration = Arr::get($this->mailPayload, 'attendance.shift.max_overtime_readable');
                $holidayPolicy = Arr::get($this->mailPayload, 'attendance.shift.holiday_policy.text');

                //Attendance
                $attendanceFirstIn = Arr::get($this->mailPayload, 'attendance.first_in');
                $attendanceLunchOut = Arr::get($this->mailPayload, 'attendance.lunch_out');
                $attendanceLunchIn = Arr::get($this->mailPayload, 'attendance.lunch_in');
                $attendanceLastOut = Arr::get($this->mailPayload, 'attendance.last_out');

                //Adjustment
                $adjustmentAttendanceFirstIn = Arr::get($this->mailPayload, 'first_in');
                $adjustmentAttendanceLunchOut = Arr::get($this->mailPayload, 'lunch_out');
                $adjustmentAttendanceLunchIn = Arr::get($this->mailPayload, 'lunch_in');
                $adjustmentAttendanceLastOut = Arr::get($this->mailPayload, 'last_out');
                $remarks = Arr::get($this->mailPayload, 'remarks');

                $this->mailPayloadSummary = [
                    'title' => $this->title,
                    'request_number' => $number,
                    'request_number_subtitle' => $numberSubtitle,
                    'requested_by_username' => $requestedByUsername,
                    'date_requested_diff' => $dateRequestedDiff,
                    'attendance_of' => $attendanceOf,

                    //Schedule
                    'shift_requires_lunch_out_and_in' => $attendanceShiftRequiresLunchOutAndIn,

                    'schedule_work_period' => $scheduleWorkPeriod,
                    'shift_work_start_grace' => $shiftWorkStartGrace,
                    'schedule_lunch_period' => $scheduleLunchPeriod,
                    'shift_lunch_start_grace' => $shiftLunchStartGrace,

                    'schedule_total_duration' => $scheduleTotalDuration,
                    'overtime_max_duration' => $overtimeMaxDuration,
                    'holiday_policy' => $holidayPolicy,

                    //Attendance
                    'attendance_first_in' => $attendanceFirstIn,
                    'attendance_lunch_out' => $attendanceLunchOut,
                    'attendance_lunch_in' => $attendanceLunchIn,
                    'attendance_last_out' => $attendanceLastOut,

                    //Adjustment
                    'adjustment_attendance_first_in' => $adjustmentAttendanceFirstIn,
                    'adjustment_attendance_lunch_out' => $adjustmentAttendanceLunchOut,
                    'adjustment_attendance_lunch_in' => $adjustmentAttendanceLunchIn,
                    'adjustment_attendance_last_out' => $adjustmentAttendanceLastOut,
                    'remarks' => $remarks,
                ];

                break;

            case 'overtime_request':

                $number = Arr::get($this->mailPayload, 'number');
                $numberSubtitle = Arr::get($this->mailPayload, 'attendance.date_readable') .
                    ' ' .
                    Arr::get($this->mailPayload, 'attendance.week_day_name') .
                    ' Overtime request';
                $requestedByUsername = Arr::get($this->mailPayload, 'requested_by.username');
                $dateRequestedDiff = Arr::get($this->mailPayload, 'date_requested_diff');
                $attendanceOf = '(' . Arr::get($this->mailPayload, 'attendance.employee.number') . ')' .
                    ' ' .
                    Arr::get($this->mailPayload, 'attendance.employee.full_name');

                //Schedule
                $scheduleWorkPeriod = Arr::get($this->mailPayload, 'attendance.shift_schedule.work_start') . ' - ' .
                    Arr::get($this->mailPayload, 'attendance.shift_schedule.work_end') .
                    '(' . Arr::get($this->mailPayload, 'attendance.shift_schedule.timezone') . ')';

                $scheduleTotalDuration = Arr::get($this->mailPayload, 'attendance.shift_schedule.total_work_hours_with_breaks');
                $overtimeMaxDuration = Arr::get($this->mailPayload, 'attendance.shift.max_overtime_readable');
                $holidayPolicy = Arr::get($this->mailPayload, 'attendance.shift.holiday_policy.text');

                //Attendance
                $attendanceLastOut = Arr::get($this->mailPayload, 'attendance.last_out');

                //Overtime
                $overtimeStart = Arr::get($this->mailPayload, 'start');
                $overtimeEnd = Arr::get($this->mailPayload, 'end');
                $totalDuration = Arr::get($this->mailPayload, 'duration_readable');
                $remarks = Arr::get($this->mailPayload, 'remarks');

                $this->mailPayloadSummary = [
                    'title' => $this->title,
                    'request_number' => $number,
                    'request_number_subtitle' => $numberSubtitle,
                    'requested_by_username' => $requestedByUsername,
                    'date_requested_diff' => $dateRequestedDiff,
                    'attendance_of' => $attendanceOf,

                    //Schedule
                    'schedule_work_period' => $scheduleWorkPeriod,

                    'schedule_total_duration' => $scheduleTotalDuration,
                    'overtime_max_duration' => $overtimeMaxDuration,
                    'holiday_policy' => $holidayPolicy,

                    //Attendance
                    'attendance_last_out' => $attendanceLastOut,

                    //Overtime
                    'overtime_start' => $overtimeStart,
                    'overtime_end' => $overtimeEnd,
                    'total_duration' => $totalDuration,
                    'remarks' => $remarks,
                ];

                break;

            case 'leave_request':

                $number = Arr::get($this->mailPayload, 'number');
                $numberSubtitle = 'Leave request';
                $requestedByUsername = Arr::get($this->mailPayload, 'requested_by.username');
                $dateRequestedDiff = Arr::get($this->mailPayload, 'date_requested_diff');

                //Leave
                $employee = '(' . Arr::get($this->mailPayload, 'employee.number') . ')' .
                ' ' .
                Arr::get($this->mailPayload, 'employee.full_name');

                $leaveType = '(' . Arr::get($this->mailPayload, 'leave_type.code') . ')' .
                ' ' .
                Arr::get($this->mailPayload, 'leave_type.name');

                $leaveDateFrom = Arr::get($this->mailPayload, 'date_from_readable');
                $leaveDateTo = Arr::get($this->mailPayload, 'date_to_readable');

                $leaveDateRange = $leaveDateFrom == $leaveDateTo ? $leaveDateFrom : $leaveDateFrom . ' to ' . $leaveDateTo;
                $remarks = Arr::get($this->mailPayload, 'remarks');

                $this->mailPayloadSummary = [
                    'title' => $this->title,
                    'request_number' => $number,
                    'request_number_subtitle' => $numberSubtitle,
                    'requested_by_username' => $requestedByUsername,
                    'date_requested_diff' => $dateRequestedDiff,

                    //Leave
                    'employee' => $employee,
                    'leave_type' => $leaveType,
                    'leave_date_range' => $leaveDateRange,
                    'remarks' => $remarks,
                ];

                break;

            case 'payroll_request':

                $number = Arr::get($this->mailPayload, 'number');
                $numberSubtitle = 'Payroll request';
                $requestedByUsername = Arr::get($this->mailPayload, 'requested_by.username');
                $dateRequestedDiff = Arr::get($this->mailPayload, 'date_requested_diff');

                //Payroll
                $payrollNumber = Arr::get($this->mailPayload, 'payroll.number');
                $payrollRemarks = Arr::get($this->mailPayload, 'payroll.remarks');
                $payrollMonth = Arr::get($this->mailPayload, 'payroll.year') .
                    ' ' . Arr::get($this->mailPayload, 'payroll.month_readable');
                $payrollSequence = Arr::get($this->mailPayload, 'payroll.pay_frequency.text') .
                    ' ' . Arr::get($this->mailPayload, 'payroll.frequency_sequence.text');
                $payrollPeriod = Arr::get($this->mailPayload, 'payroll.date_range_readable');

                $payrollTotalEmployerContributionShare = Arr::get($this->mailPayload, 'payroll.total_employer_contribution_share_formatted');
                $payrollTotalTaxWithheld = Arr::get($this->mailPayload, 'payroll.total_withholding_tax_formatted');
                $payrollTotalTaxRefund = Arr::get($this->mailPayload, 'payroll.total_tax_refund_formatted');
                $payrollTotalNetDue = Arr::get($this->mailPayload, 'payroll.total_net_formatted');

                $this->mailPayloadSummary = [
                    'title' => $this->title,
                    'request_number' => $number,
                    'request_number_subtitle' => $numberSubtitle,
                    'requested_by_username' => $requestedByUsername,
                    'date_requested_diff' => $dateRequestedDiff,

                    //Leave
                    'payroll_number' => $payrollNumber,
                    'payroll_remarks' => $payrollRemarks,
                    'payroll_month' => $payrollMonth,
                    'payroll_sequence' => $payrollSequence,
                    'payroll_period' => $payrollPeriod,
                    'payroll_total_employer_contribution_share' => $payrollTotalEmployerContributionShare,
                    'payroll_total_tax_withheld' => $payrollTotalTaxWithheld,
                    'payroll_total_tax_refund' => $payrollTotalTaxRefund,
                    'payroll_total_net_due' => $payrollTotalNetDue,
                ];

                break;
        }
    }
}
