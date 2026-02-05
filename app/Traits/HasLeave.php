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
    public function filterLeaveDateRange($companyId, $employeeId, $shiftId, CarbonPeriod $datePeriod): array
    {
        $this->setShift($shiftId);

        $leaves = $this->getEmployeeLeaves($employeeId, $datePeriod);

        $shiftHolidayPolicyIsDayOff = $this->shiftHolidayPolicy == ShiftHolidayPolicy::DAY_OFF;

        $filteredDates = $this->processDatePeriod($datePeriod, $leaves, $companyId, $shiftHolidayPolicyIsDayOff, 'filter');

        return collect($filteredDates)->map(function ($date){
            return $date->toDateString();
        })->values()->toArray();
    }

    public function leaveInquiryMap($companyId, $employeeId, $shiftId, CarbonPeriod $datePeriod): array
    {
        $this->setShift($shiftId);

        $leaves = $this->getEmployeeLeaves($employeeId, $datePeriod);

        $shiftHolidayPolicyIsDayOff = $this->shiftHolidayPolicy == ShiftHolidayPolicy::DAY_OFF;

        return $this->processDatePeriod($datePeriod, $leaves, $companyId, $shiftHolidayPolicyIsDayOff, 'map');
    }

    private function getEmployeeLeaves($employeeId, CarbonPeriod $datePeriod): Collection
    {
        $leaves = Leave::query()
            ->where('employee_id', $employeeId)
            ->whereBetween('date', [$datePeriod->getStartDate(), $datePeriod->getEndDate()])
            ->get();

        return collect(Fractal::collection($leaves, LeaveBasicTransformer::class)['data']);
    }

    private function processDatePeriod(CarbonPeriod $datePeriod, Collection $leaves, $companyId, bool $shiftHolidayPolicyIsDayOff, string $operation): array
    {
        return collect($datePeriod)
            ->when($operation === 'filter',

                fn($collection) => $collection->filter(
                    function ($date) use ($leaves, $companyId, $shiftHolidayPolicyIsDayOff) {

                        $dateEvaluation = $this->evaluateDate($date, $leaves, $companyId, $shiftHolidayPolicyIsDayOff);

                        return $dateEvaluation['is_claimable'];
                    }
                ),

                fn($collection) => collect($collection->map(function ($date) use ($leaves, $companyId, $shiftHolidayPolicyIsDayOff) {

                    $dateEvaluation = $this->evaluateDate($date, $leaves, $companyId, $shiftHolidayPolicyIsDayOff);

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
