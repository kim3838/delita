<?php

namespace App\Transformers\Attendance;

use App\Blueprint\Repositories\EmployeeShiftRepository;
use App\Blueprint\Repositories\PayrollRepository;
use App\Blueprint\Repositories\SalaryStatementAttendanceRepository;
use App\Blueprint\Repositories\ShiftRepository;
use App\Blueprint\Repositories\ShiftScheduleRepository;
use App\Facades\Fractal;
use App\Models\Attendance;
use App\Models\Employee;
use App\Transformers\EmployeeShift\ItemTransformer as EmployeeShiftItemTransformer;
use App\Transformers\Payroll\BasicTransformer as PayrollBasicTransformer;
use App\Transformers\SalaryStatementAttendance\BasicListTransformer as SalaryStatementAttendanceBasicListTransformer;
use App\Transformers\Shift\ItemTransformer as ShiftItemTransformer;
use App\Transformers\ShiftSchedule\ListTransformer as ShiftScheduleListTransformer;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(Attendance $attendance): array
    {
        $employee = Employee::query()->find($attendance->employee_id);

        $attendanceShift = null;
        $attendanceShiftAssignment = null;
        $shiftSchedule = null;

        if(!empty($attendance->id)){

            $shiftHydrated = App::make(ShiftRepository::class)->hydrateItem([
                'id' => $attendance->shift_id,
                'code' => $attendance->shift_code,
                'name' => $attendance->shift_name,
                'type' => $attendance->shift_type,
                'holiday_policy' => $attendance->shift_holiday_policy,
                'work_start_grace_time' => $attendance->shift_work_start_grace_time,
                'require_lunch_time_in_and_out' => $attendance->shift_require_lunch_time_in_and_out,
                'lunch_start_grace_time' => $attendance->shift_lunch_start_grace_time,
                'max_overtime' => $attendance->shift_max_overtime,
            ]);

            $attendanceShift = Fractal::item($shiftHydrated, ShiftItemTransformer::class);

            $shiftAssignmentHydrated = App::make(EmployeeShiftRepository::class)->hydrateItem([
                'start_date' => $attendance->shift_assignment_start_date,
                'stated_shift_end_date' => $attendance->shift_assignment_stated_shift_end_date,
                'end_date' => $attendance->shift_assignment_end_date,
            ]);

            $attendanceShiftAssignment = Fractal::item($shiftAssignmentHydrated, EmployeeShiftItemTransformer::class);

            $shiftScheduleHydrated = App::make(ShiftScheduleRepository::class)->hydrateItem([
                'week_day' => $attendance->shift_schedule_week_day,
                'is_rest_day' => $attendance->shift_schedule_is_rest_day,
                'is_day_off' => $attendance->shift_schedule_is_day_off,
                'is_flexible' => $attendance->shift_schedule_is_flexible,
                'timezone' => $attendance->shift_schedule_timezone,
                'work_start' => $attendance->shift_schedule_work_start,
                'work_end' => $attendance->shift_schedule_work_end,
                'total_work_hours_with_breaks' => $attendance->shift_schedule_total_work_hours_with_breaks,
                'has_lunch_break' => $attendance->shift_schedule_has_lunch_break,
                'lunch_break_start' => $attendance->shift_schedule_lunch_break_start,
                'lunch_break_end' => $attendance->shift_schedule_lunch_break_end,
                'total_lunch_break_hours' => $attendance->shift_schedule_total_lunch_break_hours,
            ]);

            $shiftSchedule = Fractal::item($shiftScheduleHydrated, ShiftScheduleListTransformer::class);
        }

        $payroll = null;
        $salaryStatementAttendance = null;

        if(!empty($attendance->salary_statement_attendance_payroll_id)){
            $payrollHydrated = App::make(PayrollRepository::class)->hydrateItem([
                'id' => $attendance->salary_statement_attendance_payroll_id,
                'ulid' => $attendance->salary_statement_attendance_payroll_ulid,
                'company_id' => $attendance->salary_statement_attendance_payroll_company_id,
                'number' => $attendance->salary_statement_attendance_payroll_number,
                'year' => $attendance->salary_statement_attendance_payroll_year,
                'month' => $attendance->salary_statement_attendance_payroll_month,
                'pay_frequency' => $attendance->salary_statement_attendance_payroll_pay_frequency,
                'frequency_sequence' => $attendance->salary_statement_attendance_payroll_frequency_sequence,
                'start_date' => $attendance->salary_statement_attendance_payroll_start_date,
                'end_date' => $attendance->salary_statement_attendance_payroll_end_date,
                'remarks' => $attendance->salary_statement_attendance_payroll_remarks,
                'status' => $attendance->salary_statement_attendance_payroll_status,
            ]);

            $payroll = Fractal::item($payrollHydrated, PayrollBasicTransformer::class);
        }

        if(!empty($attendance->salary_statement_attendance_id)){

            $salaryStatementAttendanceHydrated = App::make(SalaryStatementAttendanceRepository::class)->hydrateItem([
                'id' => $attendance->salary_statement_attendance_id,
                'ulid' => $attendance->salary_statement_attendance_ulid,
                'salary_statement_id' => $attendance->salary_statement_attendance_salary_statement_id,
                'attendance_id' => $attendance->salary_statement_attendance_attendance_id,
                'date' => $attendance->salary_statement_attendance_date,
                'status' => $attendance->salary_statement_attendance_status,
                'day_type' => $attendance->salary_statement_attendance_day_type,
            ]);

            $salaryStatementAttendance = Fractal::item($salaryStatementAttendanceHydrated, SalaryStatementAttendanceBasicListTransformer::class);
        }

        $dateReadable = $attendance->date
            ? $attendance->date?->format('F j, Y')
            : ($attendance->date_series_date ? $attendance->date_series_date?->format('F j, Y') : null);

        $weekDayName = $attendance->date?->format('l') ?? $attendance->date_series_date?->format('l');

        return [
            'row_number' => $attendance->row_number,
            'id' => $attendance->id ?? $attendance->proxy_id,
            'ulid' => $attendance->ulid,
            'employee_id' => $attendance->employee_id,
            'payroll' => $payroll,
            'salary_statement_attendance' => $salaryStatementAttendance,
            'date' => $attendance->date?->toDateString() ?? $attendance->date_series_date?->toDateString(),
            'date_readable' => $dateReadable,
            'week_day_name' => $weekDayName,
            'first_in' => $attendance->first_in?->format('Y-m-d H:i'),
            'lunch_out' => $attendance->lunch_out?->format('Y-m-d H:i'),
            'lunch_in' => $attendance->lunch_in?->format('Y-m-d H:i'),
            'last_out' => $attendance->last_out?->format('Y-m-d H:i'),
            'status' => $attendance->status?->toArray(),
            'employee' => [
                'number' => $employee?->number,
                'full_name' => $employee?->full_name,
                'department' => $employee?->departments?->first(),
                'designation' => $employee?->designation,
            ],
            'shift' => $attendanceShift,
            'shift_schedule' => $shiftSchedule,
            'shift_assignment' => $attendanceShiftAssignment
        ];
    }
}
