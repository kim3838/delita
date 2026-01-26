<?php

namespace App\Http\Controllers;

use App\Enums\ShiftHolidayPolicy;
use App\Exceptions\UnexpectedException;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\LeaveDateRangeFilter\LeaveDateRangeFilterRequest;
use App\Models\Leave;
use App\Traits\WorkPeriod;
use App\Transformers\Leave\BasicTransformer as LeaveBasicTransformer;
use Carbon\CarbonPeriod;

class LeaveDateRangeFilterController extends Controller
{
    use WorkPeriod;

    /**
     * @throws UnexpectedException
     */
    public function index(LeaveDateRangeFilterRequest $request)
    {
        if($request->expectsJson()){

            $companyId = $request->validated()['company_id'];
            $employeeId = $request->validated()['employee_id'];
            $shiftId = $request->validated()['shift_id'];
            $leaveTypeId = $request->validated()['leave_type_id'];
            $dateFrom = $request->validated()['date_from'];
            $dateTo = $request->validated()['date_to'];

            $datePeriod = CarbonPeriod::create($dateFrom, $dateTo);

            $this->setShift($shiftId);
            $shiftHolidayPolicyIsDayOff = $this->shiftHolidayPolicy == ShiftHolidayPolicy::DAY_OFF;

            $leaves = Leave::query()
                ->where('employee_id', $employeeId)
                ->where('leave_type_id', $leaveTypeId)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->get();

            $leaves = collect(Fractal::collection($leaves, LeaveBasicTransformer::class)['data']);

            $filteredDates = collect($datePeriod)->filter(function ($date) use ($leaves, $companyId, $shiftHolidayPolicyIsDayOff){
                $this->setAttendanceSchedule($date);

                $isAttendanceDateIsHoliday = !empty($this->getCompanyHolidayByDate($date->toDateString(), $companyId));

                $dayOff = $this->attendanceScheduleIsDayOff;
                $attendanceDateIsHolidayAndShiftHolidayPolicyIsDayOff = ($isAttendanceDateIsHoliday && $shiftHolidayPolicyIsDayOff);
                $dayOffOrHoliday = $dayOff || $attendanceDateIsHolidayAndShiftHolidayPolicyIsDayOff;

                $hasLeave = $leaves->where('date', $date->toDateString())->isNotEmpty();

                return !$dayOffOrHoliday && !$hasLeave;
            });

            return ResponseJson::successfulResponse([
                'dates' => $filteredDates->map(function ($date){
                    return $date->toDateString();
                })->values()->all()
            ]);
        }

        abort(404);
    }
}
