<?php

namespace App\Concrete;

use App\Blueprint\AttendanceSplitterInterface;
use App\Blueprint\EmployeeServiceInterface;
use App\Blueprint\PayrollServiceInterface;
use App\Blueprint\Repositories\AttendanceRepository;
use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\LeaveRepository;
use App\Blueprint\Repositories\ShiftScheduleRepository;
use App\Blueprint\WorkPeriodServiceInterface;
use App\Enums\ShiftBreakDownSplitType;
use App\Enums\ShiftHolidayPolicy;
use App\Exceptions\UnexpectedException;
use App\Facades\Fractal;
use App\Models\Company;
use App\Models\EmployeeShift;
use App\Transformers\EmployeeShift\PatchableTransformer as EmployeeShiftPatchableTransformer;
use App\Transformers\Leave\BasicTransformer as LeaveBasicTransformer;
use App\Transformers\Shift\PatchableTransformer as ShiftPatchableTransformer;
use App\Transformers\ShiftSchedule\PatchableTransformer as ShiftSchedulePatchableTransformer;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AutoCreateAttendanceConcrete
{
    protected ?Company $company;

    /**
     * @throws UnexpectedException
     */
    public function __invoke($validated): array
    {
        $errors = [];

        $workPeriodService = app(WorkPeriodServiceInterface::class);

        $companyId = data_get($validated, 'company_id');
        $employeeIds = data_get($validated, 'employee_ids', []);
        $employeeGroupIds = data_get($validated, 'assigned_employee_group_ids', []);
        $dateFrom = Carbon::parse(data_get($validated, 'date_from'));
        $dateTo = Carbon::parse(data_get($validated, 'date_to'));
        $replaceExistingAttendance = data_get($validated, 'replace_existing_attendance', true);

        $this->company = Company::findOrFail($companyId);
        $workPeriodService->setCompany($this->company);

        //Set company formula settings
        $workPeriodService->resolveCompanyFormulaSettings();

        //Set company night hours
        $workPeriodService->resolveCompanyNightHoursFromBasicPayFormulaSettings();

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
            $employeeService = app(EmployeeServiceInterface::class, [$employee]);
            $employeeShifts = EmployeeShift::where('employee_id', $employee->id)->get();

            if(empty($employee)){
                throw new UnexpectedException("Employee not found: C.Auto create attendance [" . __LINE__ . "]");
            }

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

                //Skip date if payroll generated
                $isDateOnAnyPayrollStatementAttendance = $payrollService->isDateOnAnyPayrollStatementAttendance($employee, $date);

                if($isDateOnAnyPayrollStatementAttendance){
                    $errors[] = [
                        'employee_number' => $employee->number,
                        'employee_full_name' => $employee->full_name,
                        'date' => $date->toDateString(),
                        'error' => 'Date is payroll generated.'
                    ];
                    continue;
                }

                $employeeShiftPivot = $employeeService->getEmployeeShiftFromEmployeeShiftCollection($employeeShifts, $date);

                //Skip if employee is not assigned to any shift on this date
                if(empty($employeeShiftPivot)){
                    $errors[] = [
                        'employee_number' => $employee->number,
                        'employee_full_name' => $employee->full_name,
                        'date' => $date->toDateString(),
                        'error' => 'Shift not found.'
                    ];
                    continue;
                }

                $employeeShift = $employeeShiftPivot->shift;

                $leave = collect($employeeDatePeriodLeaves)->where('date', $date->toDateString());
                $hasLeave = $leave->isNotEmpty();

                //Skip if employee has a leave on this date
                if($hasLeave) continue;

                $workPeriodService->setShift($employeeShift);
                $workPeriodService->setAttendanceSchedule($date);

                $dayOff = $workPeriodService->attendanceScheduleIsDayOff;
                $holiday = $workPeriodService->getCompanyHolidayByDate($date, $this->company->id);
                $holidayType = !empty($holiday) ? $holiday->type : null;

                $isDateIsHoliday = !empty($holidayType);
                $shiftHolidayPolicyIsDayOff = $workPeriodService->shiftHolidayPolicy == ShiftHolidayPolicy::DAY_OFF;
                $attendanceDateIsHolidayAndShiftHolidayPolicyIsDayOff = ($isDateIsHoliday && $shiftHolidayPolicyIsDayOff);
                $dayOffOrHolidayDayOff = $dayOff || $attendanceDateIsHolidayAndShiftHolidayPolicyIsDayOff;

                //Skip if day type is day off or holiday and shift holiday policy is day off
                if($dayOffOrHolidayDayOff) continue;

                $startingDateHolidayType = $workPeriodService->getDateHolidayType($date->toDateString());
                $startingDateIsRestDay = in_array($date->dayOfWeek, $workPeriodService->restDays);

                $schedule = $workPeriodService->attendanceSchedule;
                $schedule = $workPeriodService->parseSchedule($schedule, $date);

                $workPeriods = $workPeriodService->calculateWorkPeriods($schedule);
                list($scheduleBreakdown) = $workPeriodService->breakdownWorkPeriods($workPeriods, $startingDateIsRestDay, $startingDateHolidayType);
                $hasLunchBreak = $workPeriodService->shiftRequireLunchOutAndIn();

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

                $shiftScheduleHydrated = $shiftSchedule->hydrateItem($workPeriodService->attendanceSchedule);

                $shiftDetail = [
                    ...Fractal::item($employeeShiftPivot, EmployeeShiftPatchableTransformer::class),
                    ...Fractal::item($workPeriodService->shift, ShiftPatchableTransformer::class),
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
