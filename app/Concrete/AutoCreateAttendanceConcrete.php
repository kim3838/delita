<?php

namespace App\Concrete;

use App\Blueprint\AttendanceSplitterInterface;
use App\Blueprint\PayrollServiceInterface;
use App\Blueprint\Repositories\AttendanceRepository;
use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\LeaveRepository;
use App\Blueprint\Repositories\ShiftScheduleRepository;
use App\Enums\ShiftBreakDownSplitType;
use App\Enums\ShiftHolidayPolicy;
use App\Exceptions\UnexpectedException;
use App\Facades\Fractal;
use App\Models\Company;
use App\Traits\WorkPeriod;
use App\Transformers\EmployeeShift\PatchableTransformer as EmployeeShiftPatchableTransformer;
use App\Transformers\Leave\BasicTransformer as LeaveBasicTransformer;
use App\Transformers\Shift\PatchableTransformer as ShiftPatchableTransformer;
use App\Transformers\ShiftSchedule\PatchableTransformer as ShiftSchedulePatchableTransformer;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AutoCreateAttendanceConcrete
{
    protected ?Company $company;

    use WorkPeriod;

    /**
     * @throws UnexpectedException
     */
    public function __invoke($validated): array
    {
        $errors = [];

        $companyId = data_get($validated, 'company_id');
        $employeeIds = data_get($validated, 'employee_ids', []);
        $employeeGroupIds = data_get($validated, 'assigned_employee_group_ids', []);
        $dateFrom = Carbon::parse(data_get($validated, 'date_from'));
        $dateTo = Carbon::parse(data_get($validated, 'date_to'));
        $replaceExistingAttendance = data_get($validated, 'replace_existing_attendance', true);

        $this->company = Company::findOrFail($companyId);

        //Set company formula settings
        $this->resolveCompanyFormulaSettings();

        //Set company night hours
        $this->resolveCompanyNightHoursFromBasicPayFormulaSettings();

        $employeeRepositoryFilters = (object) [
            'company_id' => $companyId,
            'assigned_employee_group_ids' => $employeeGroupIds,
            ...(empty($employeeGroupIds) ? [
                'employee_ids' => $employeeIds
            ] : [
                'or_employee_ids' => $employeeIds
            ]),

        ];

        $attendanceRepository = app(AttendanceRepository::class);
        $attendanceSplitter = app(AttendanceSplitterInterface::class, [$this->company]);
        $shiftSchedule = app(ShiftScheduleRepository::class);

        $payrollService = app(PayrollServiceInterface::class, [$this->company]);

        $employeeLazyCollection = app(EmployeeRepository::class)->queryBuilderCursor($employeeRepositoryFilters);

        foreach($employeeLazyCollection as $employee){

            $employee = app(EmployeeRepository::class)->hydrateItem($employee);
            $employeeShift = $employee->shifts->first();
            $employeeDatePeriodLeaves = app(LeaveRepository::class)
                ->model()::where('employee_id', $employee->id)
                ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
                ->get();

            $employeeDatePeriodLeaves = Fractal::collection(
                $employeeDatePeriodLeaves,
                LeaveBasicTransformer::class
            )['data'];

            $datePeriod = CarbonPeriod::create($dateFrom, $dateTo);

            foreach($datePeriod as $date){

                //Skip if employee has payroll attendance on this date
                $isDateOnAnyPayrollStatementAttendance = $payrollService->isDateOnAnyPayrollStatementAttendance($employee, $date);
                if($isDateOnAnyPayrollStatementAttendance){
                    $errors[] = [
                        'employee_number' => $employee->number,
                        'employee_full_name' => $employee->fullName,
                        'date' => $date->toDateString(),
                        'error' => 'Date is payroll generated.'
                    ];
                    continue;
                }

                //Skip if employee is not assigned to any shift
                if(empty($employeeShift)){
                    $errors[] = [
                        'employee_number' => $employee->number,
                        'employee_full_name' => $employee->fullName,
                        'date' => $date->toDateString(),
                        'error' => 'Shift not found.'
                    ];
                    continue;
                }

                //Skip if employee shift is not assigned to a date range
                if($employeeShift->pivot->stated_shift_end_date){

                    if(!$date->between($employeeShift->pivot->start_date, $employeeShift->pivot->end_date)){
                        $errors[] = [
                            'employee_number' => $employee->number,
                            'employee_full_name' => $employee->fullName,
                            'date' => $date->toDateString(),
                            'error' => 'Date is not in date range of employee shift assignment.'
                        ];
                        continue;
                    }
                }

                $leave = collect($employeeDatePeriodLeaves)->where('date', $date->toDateString());
                $hasLeave = $leave->isNotEmpty();

                //Skip if employee has a leave on this date
                if($hasLeave){
                    $errors[] = [
                        'employee_number' => $employee->number,
                        'employee_full_name' => $employee->fullName,
                        'date' => $date->toDateString(),
                        'error' => 'Employee is on leave.'
                    ];
                    continue;
                }

                $this->setShift($employeeShift);
                $this->setAttendanceSchedule($date);

                $dayOff = $this->attendanceScheduleIsDayOff;
                $holiday = $this->getCompanyHolidayByDate($date, $this->company->id);
                $holidayType = !empty($holiday) ? $holiday->type : null;

                $isDateIsHoliday = !empty($holidayType);
                $shiftHolidayPolicyIsDayOff = $this->shiftHolidayPolicy == ShiftHolidayPolicy::DAY_OFF;
                $attendanceDateIsHolidayAndShiftHolidayPolicyIsDayOff = ($isDateIsHoliday && $shiftHolidayPolicyIsDayOff);
                $dayOffOrHolidayDayOff = $dayOff || $attendanceDateIsHolidayAndShiftHolidayPolicyIsDayOff;

                //Skip if day type is day off or holiday and shift holiday policy is day off
                if($dayOffOrHolidayDayOff) continue;

                $startingDateHolidayType = $this->getDateHolidayType($date->toDateString());
                $startingDateIsRestDay = in_array($date->dayOfWeek, $this->restDays);

                $schedule = $this->attendanceSchedule;
                $schedule = $this->parseSchedule($schedule, $date);

                $workPeriods = $this->calculateWorkPeriods($schedule);
                list($scheduleBreakdown) = $this->breakdownWorkPeriods($workPeriods, $startingDateIsRestDay, $startingDateHolidayType);
                $hasLunchBreak = $this->shiftRequireLunchOutAndIn();

                $firstWorkSplit = null;
                $firstLunchSplit = null;
                $lastLunchSplit = null;
                $lastWorkSplit = null;
                $scheduleBreakdownCollection = collect($scheduleBreakdown)->sortBy('order');

                foreach ($scheduleBreakdownCollection as $scheduleBreakdownItem) {

                    if(empty($firstWorkSplit) && $scheduleBreakdownItem['split_type'] == ShiftBreakDownSplitType::WORK){
                        $firstWorkSplit = $scheduleBreakdownItem;
                    }

                    if($hasLunchBreak && empty($firstLunchSplit) && $scheduleBreakdownItem['split_type'] == ShiftBreakDownSplitType::LUNCH){
                        $firstLunchSplit = $scheduleBreakdownItem;
                    }
                }

                $scheduleBreakdownLatestCollection = collect($scheduleBreakdown)->sortByDesc('order');

                foreach ($scheduleBreakdownLatestCollection as $scheduleBreakdownLatestItem) {

                    if(empty($lastWorkSplit) && $scheduleBreakdownLatestItem['split_type'] == ShiftBreakDownSplitType::WORK){
                        $lastWorkSplit = $scheduleBreakdownLatestItem;
                    }

                    if($hasLunchBreak && empty($lastLunchSplit) && $scheduleBreakdownLatestItem['split_type'] == ShiftBreakDownSplitType::LUNCH){
                        $lastLunchSplit = $scheduleBreakdownLatestItem;
                    }
                }

                if(empty($employeeShift->pivot)){
                    throw new UnexpectedException("Attendance shift assignment not found: C.Auto create attendance [" . __LINE__ . "]");
                }

                $shiftScheduleHydrated = $shiftSchedule->hydrateItem($this->attendanceSchedule);

                $shiftDetail = [
                    ...Fractal::item($employeeShift->pivot, EmployeeShiftPatchableTransformer::class),
                    ...Fractal::item($this->shift, ShiftPatchableTransformer::class),
                    ...Fractal::item($shiftScheduleHydrated, ShiftSchedulePatchableTransformer::class)
                ];

                $firstIn = $firstWorkSplit['date'] . ' ' . $firstWorkSplit['split_start'];
                $lunchOut = $hasLunchBreak ? ($firstLunchSplit['date'] . ' ' . $firstLunchSplit['split_start']) : null;
                $lunchIn = $hasLunchBreak ? ($lastLunchSplit['date'] . ' ' . $lastLunchSplit['split_end']) : null;
                $lastOut = $lastWorkSplit['date'] . ' ' . $lastWorkSplit['split_end'];

                $putAttendance = [
                    'employee_id' => $employee->id,
                    'shift_id' => $employeeShift->id,
                    'date' => $date->toDateString(),
                    'first_in' => $firstIn,
                    'lunch_out' => $lunchOut,
                    'lunch_in' => $lunchIn,
                    'last_out' => $lastOut,
                ];

                $existing = $attendanceRepository->model()::query()
                    ->where('employee_id', $employee->id)
                    ->where('shift_id', $employeeShift->id)
                    ->where('date', $date->toDateString())
                    ->first();

                $attendance = $existing ?: $attendanceRepository->model()::create($putAttendance);

                if($existing){

                    if($replaceExistingAttendance){
                        $attendanceRepository->update($existing->ulid, $putAttendance, $attendanceSplitter);
                    }

                } else {
                    //Generate attendance splitter on newly created attendance
                    $attendanceSplitter->generate($attendance);
                }

                if(!$existing || $replaceExistingAttendance){

                    $attendance->shiftDetail()->delete();

                    $attendance->shiftDetail()->create($shiftDetail);
                }
            }
        }

        return $errors;
    }
}
