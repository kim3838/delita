<?php

namespace App\Traits;

use App\Enums\ShiftHolidayPolicy;
use App\Exceptions\UnexpectedException;
use App\Facades\Fractal;
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
    public function filterDateRange($companyId, $employeeId, $shiftId, $leaveTypeId, CarbonPeriod $datePeriod): Collection
    {
        $this->setShift($shiftId);

        $leaves = $this->getEmployeeLeaves($employeeId, $leaveTypeId, $datePeriod);

        $shiftHolidayPolicyIsDayOff = $this->shiftHolidayPolicy == ShiftHolidayPolicy::DAY_OFF;

        return $this->processDatePeriod($datePeriod, $leaves, $companyId, $shiftHolidayPolicyIsDayOff, 'filter');
    }

    public function inquiryMap($companyId, $employeeId, $shiftId, $leaveTypeId, CarbonPeriod $datePeriod): array
    {
        $this->setShift($shiftId);

        $leaves = $this->getEmployeeLeaves($employeeId, $leaveTypeId, $datePeriod);

        $shiftHolidayPolicyIsDayOff = $this->shiftHolidayPolicy == ShiftHolidayPolicy::DAY_OFF;

        return $this->processDatePeriod($datePeriod, $leaves, $companyId, $shiftHolidayPolicyIsDayOff, 'map')->toArray();
    }

    private function getEmployeeLeaves($employeeId, $leaveTypeId, CarbonPeriod $datePeriod): Collection
    {
        $leaves = Leave::query()
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->whereBetween('date', [$datePeriod->getStartDate(), $datePeriod->getEndDate()])
            ->get();

        return collect(Fractal::collection($leaves, LeaveBasicTransformer::class)['data']);
    }

    private function processDatePeriod(CarbonPeriod $datePeriod, Collection $leaves, $companyId, bool $shiftHolidayPolicyIsDayOff, string $operation): Collection
    {
        return collect($datePeriod)
            ->when($operation === 'filter',

                fn($collection) => $collection->filter(
                    function ($date) use ($leaves, $companyId, $shiftHolidayPolicyIsDayOff) {

                        $dateEvaluation = $this->evaluateDate($date, $leaves, $companyId, $shiftHolidayPolicyIsDayOff);

                        return $dateEvaluation['is_claimable'];
                    }
                ),

                fn($collection) => $collection->map(function ($date) use ($leaves, $companyId, $shiftHolidayPolicyIsDayOff) {

                    $dateEvaluation = $this->evaluateDate($date, $leaves, $companyId, $shiftHolidayPolicyIsDayOff);

                    return [
                        'date' => $date->toDateString(),
                        'message' => $dateEvaluation['message'],
                        'is_claimable' => $dateEvaluation['is_claimable'],
                    ];
                })
        );
    }

    /**
     * @throws UnexpectedException
     */
    private function evaluateDate($date, Collection $leaves, $companyId, bool $shiftHolidayPolicyIsDayOff): array
    {
        $this->setAttendanceSchedule($date);

        $isAttendanceDateIsHoliday = !empty($this->getCompanyHolidayByDate($date->toDateString(), $companyId));
        $dayOff = $this->attendanceScheduleIsDayOff;
        $attendanceDateIsHolidayAndShiftHolidayPolicyIsDayOff = ($isAttendanceDateIsHoliday && $shiftHolidayPolicyIsDayOff);
        $dayOffOrHoliday = $dayOff || $attendanceDateIsHolidayAndShiftHolidayPolicyIsDayOff;
        $hasLeave = $leaves->where('date', $date->toDateString())->isNotEmpty();

        $message = $dayOff ? 'Day off' :
            ($attendanceDateIsHolidayAndShiftHolidayPolicyIsDayOff ? 'Holiday' :
                ($hasLeave ? 'Leave claimed' : 'Claimable'));

        return [
            'message' => $message,
            'is_claimable' => !$dayOffOrHoliday && !$hasLeave,
        ];
    }
}
