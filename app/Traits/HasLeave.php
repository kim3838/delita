<?php

namespace App\Traits;

use App\Blueprint\EmployeeServiceInterface;
use App\Blueprint\PayrollServiceInterface;
use App\Enums\ShiftHolidayPolicy;
use App\Exceptions\UnexpectedException;
use App\Facades\Fractal;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Leave;
use App\Transformers\Leave\BasicTransformer as LeaveBasicTransformer;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

trait HasLeave
{
    use WorkPeriod;

    /**
     * @throws UnexpectedException
     */
    public function filterLeaveDateRange($companyId, $employeeId, $shiftId, CarbonPeriod $datePeriod): array
    {
        $this->setShift($shiftId);

        $leaves = $this->getEmployeeLeaves($employeeId, $datePeriod);

        $shiftHolidayPolicyIsDayOff = $this->shiftHolidayPolicy == ShiftHolidayPolicy::DAY_OFF;

        $filteredDates = $this->processDatePeriod($datePeriod, $leaves, $companyId, $employeeId, $shiftHolidayPolicyIsDayOff, 'filter');

        return collect($filteredDates)->map(function ($date){
            return $date->toDateString();
        })->values()->toArray();
    }

    /**
     * @throws UnexpectedException
     */
    public function leaveInquiryMap($companyId, $employeeId, $shiftId, CarbonPeriod $datePeriod): array
    {
        $this->setShift($shiftId);

        $leaves = $this->getEmployeeLeaves($employeeId, $datePeriod);

        $shiftHolidayPolicyIsDayOff = $this->shiftHolidayPolicy == ShiftHolidayPolicy::DAY_OFF;

        return $this->processDatePeriod($datePeriod, $leaves, $companyId, $employeeId, $shiftHolidayPolicyIsDayOff, 'map');
    }

    private function getEmployeeLeaves($employeeId, CarbonPeriod $datePeriod): Collection
    {
        $leaves = Leave::query()
            ->where('employee_id', $employeeId)
            ->whereBetween('date', [$datePeriod->getStartDate(), $datePeriod->getEndDate()])
            ->get();

        return collect(Fractal::collection($leaves, LeaveBasicTransformer::class)['data']);
    }

    private function processDatePeriod(CarbonPeriod $datePeriod, Collection $leaves, $companyId, $employeeId, bool $shiftHolidayPolicyIsDayOff, string $operation): array
    {
        return collect($datePeriod)
            ->when($operation === 'filter',

                fn($collection) => $collection->filter(
                    function ($date) use ($leaves, $companyId, $employeeId, $shiftHolidayPolicyIsDayOff) {

                        $dateEvaluation = $this->evaluateDate($date, $leaves, $companyId, $employeeId, $shiftHolidayPolicyIsDayOff);

                        return $dateEvaluation['is_claimable'];
                    }
                ),

                fn($collection) => collect($collection->map(function ($date) use ($leaves, $companyId, $employeeId, $shiftHolidayPolicyIsDayOff) {

                    $dateEvaluation = $this->evaluateDate($date, $leaves, $companyId, $employeeId, $shiftHolidayPolicyIsDayOff);

                    return [
                        'date' => $date->toDateString(),
                        'message' => $dateEvaluation['message'],
                        'is_claimable' => $dateEvaluation['is_claimable'],
                    ];
                }))
        )->toArray();
    }

    /**
     * @throws UnexpectedException
     */
    private function evaluateDate($date, Collection $leaves, $companyId, $employeeId, bool $shiftHolidayPolicyIsDayOff): array
    {
        $this->setAttendanceSchedule($date);

        $isAttendanceDateIsHoliday = !empty($this->getCompanyHolidayByDate($date->toDateString(), $companyId));
        $dayOff = $this->attendanceScheduleIsDayOff;
        $attendanceDateIsHolidayAndShiftHolidayPolicyIsDayOff = ($isAttendanceDateIsHoliday && $shiftHolidayPolicyIsDayOff);
        $dayOffOrHoliday = $dayOff || $attendanceDateIsHolidayAndShiftHolidayPolicyIsDayOff;
        $hasLeave = $leaves->where('date', $date->toDateString())->isNotEmpty();

        $payrollService = app(PayrollServiceInterface::class, [Company::query()->find($companyId)]);
        $employee = Employee::query()->find($employeeId);

        $result = [
            'message' => 'Claimable',
            'is_claimable' => true,
        ];

        /**
         * Validate if the shift assignment is valid
         **/
        $employeeService = app(EmployeeServiceInterface::class, [$employee]);
        $employeeShifts = EmployeeShift::where('employee_id', $employee->id)
            ->where('shift_id', $this->shift->id)->get();

        $employeeShiftPivot = $employeeService->getEmployeeShiftFromEmployeeShiftCollection($employeeShifts, $date);

        if(empty($employeeShiftPivot)){
            $result['is_claimable'] = false;
            $result['message'] = 'Out of shift schedule';
        }

        if(!$result['is_claimable']) return $result;

        /**
         * Validate if date is on any payroll statement attendance
         **/
        $isDateOnAnyPayrollStatementAttendance = $payrollService->isDateOnAnyPayrollStatementAttendance($employee, $date);
        if($isDateOnAnyPayrollStatementAttendance){
            $result['is_claimable'] = false;
            $result['message'] = 'Payroll generated';
        }
        if(!$result['is_claimable']) return $result;

        /**
         * Validate if the date is a day off
         **/
        if($dayOff){
            $result['is_claimable'] = false;
            $result['message'] = 'Day off';
        }
        if(!$result['is_claimable']) return $result;

        /**
         * Validate if the date is a holiday and shift holiday policy is a day off
         **/
        if($attendanceDateIsHolidayAndShiftHolidayPolicyIsDayOff){
            $result['is_claimable'] = false;
            $result['message'] = 'Holiday';
        }
        if(!$result['is_claimable']) return $result;

        /**
         * Validate if date has leave claim
         **/
        if($hasLeave){
            $result['is_claimable'] = false;
            $result['message'] = 'Leave claimed';
        }
        if(!$result['is_claimable']) return $result;

        /**
         * Validate if attendance exists
         **/
        $attendance = Attendance::query()->where('employee_id', $employeeId)
            ->where('date', $date->toDateString())
            ->where('shift_id', $this->shift->id)->first();
        if(!empty($attendance)){
            $result['is_claimable'] = false;
            $result['message'] = 'Attendance exists';
        }

        return $result;
    }
}
