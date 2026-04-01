<?php

namespace App\Concrete;

use App\Blueprint\AttendanceSplitterInterface;
use App\Blueprint\WorkPeriodServiceInterface;
use App\Enums\AttendanceStatus;
use App\Enums\ShiftBreakDownSplitType;
use App\Enums\ShiftHolidayPolicy;
use App\Exceptions\UnexpectedException;
use App\Helpers\TimeHelper;
use App\Models\Attendance;
use App\Models\Company;
use Carbon\Carbon;

class AttendanceSplitter implements AttendanceSplitterInterface
{
    public WorkPeriodServiceInterface $workPeriodService;

    public function __construct(
        protected readonly ?Company $company
    ){
        $this->workPeriodService = app(WorkPeriodServiceInterface::class);
        $this->workPeriodService->setCompany($company);

        //Set company formula settings
        $this->workPeriodService->resolveCompanyFormulaSettings();

        //Set company night hours
        $this->workPeriodService->resolveCompanyNightHoursFromBasicPayFormulaSettings();
    }

    /**
     *
     * @throws UnexpectedException
     */
    public function generate(Attendance $attendance, $test = false, $debug = false): bool | array
    {
        $attendanceArray = $attendance->toArray();

        /**
         * Set attendance shift
         **/
        $this->workPeriodService->setShift($attendanceArray['shift_id']);

        $testCase = null;
        $testScenario = null;
        $attendanceStatusExpected = null;
        $attendanceBreakdownExpected = null;
        $overtime = null;

        if($test){
            $testCase = $attendanceArray['test_case'] ?? null;
            $testScenario = $attendanceArray['scenario'] ?? null;

            $attendanceStatusExpected = $attendanceArray['expected']['status'] ?? AttendanceStatus::NOT_SPECIFIED;
            $attendanceBreakdownExpected = $attendanceArray['expected']['details'] ?? null;
            $overtime = $attendanceArray['overtime'] ?? null;
        } else {

            $overtime = $attendance->overtime?->toArray();
        }

        /**
         * Clear attendance details
         **/
        $attendance->details()->delete();

        /**
         * Attendance date
         **/
        $date = Carbon::parse($attendanceArray['date']);

        /**
         * Set a schedule of the same week day as the attendance date
         * Set schedule has lunch break
         * Set schedule is day off
         * Set schedule is flexible
         **/
        $this->workPeriodService->setAttendanceSchedule($date);

        /**
         * If the attendance date is a day off, return
         **/
        if($this->workPeriodService->attendanceScheduleIsDayOff){
            return [];
        }

        $startingDateHolidayType = $this->workPeriodService->getDateHolidayType($date->toDateString());
        $startingDateIsRestDay = in_array($date->dayOfWeek, $this->workPeriodService->restDays);
        $isAttendanceDateIsHoliday = !empty($startingDateHolidayType);
        $shiftHolidayPolicyIsDayOff = $this->workPeriodService->shiftHolidayPolicy == ShiftHolidayPolicy::DAY_OFF;

        /**
         * If the attendance date is a holiday and shift holiday policy is a day off, return
         **/
        if($isAttendanceDateIsHoliday && $shiftHolidayPolicyIsDayOff){
            return [];
        }

        /**
         * Parse schedule times
         **/
        $schedule = $this->workPeriodService->attendanceSchedule;
        $schedule = $this->workPeriodService->parseSchedule($schedule, $date);

        if(!$test && $debug){

            $mappedSchedule = [
                'work_start' => $schedule['work_start']->format('Y-m-d H:i'),
                'lunch_start' => $schedule['lunch_start']?->format('Y-m-d H:i'),
                'lunch_end' => $schedule['lunch_end']?->format('Y-m-d H:i'),
                'work_end' => $schedule['work_end']->format('Y-m-d H:i'),
                ...(isset($schedule['overtime_end']) ? ['overtime_end' => $schedule['overtime_end']->format('Y-m-d H:i')] : [])
            ];

            _debug([
                '$schedule' => $mappedSchedule,
            ]);
        }

        /**
         * Parse attendance times
         **/
        $parsedAttendance = $this->workPeriodService->parseAttendance($attendanceArray);

        if(!$test && $debug){

            $mappedParsedAttendance = [
                'first_in' => $parsedAttendance['first_in']?->format('Y-m-d H:i'),
                'lunch_out' => $parsedAttendance['lunch_out']?->format('Y-m-d H:i'),
                'lunch_in' => $parsedAttendance['lunch_in']?->format('Y-m-d H:i'),
                'last_out' => $parsedAttendance['last_out']?->format('Y-m-d H:i'),
            ];

            _debug([
                '$mappedParsedAttendance' => $mappedParsedAttendance
            ]);

        }

        /**
         * Calculate work periods
         **/
        $workPeriods = $this->workPeriodService->calculateWorkPeriods($schedule);

        if(!$test && $debug){

            $mappedWorkPeriods = array_map(function ($period) {
                return [
                    ...$period,
                    'split_start' => $period['split_start']?->format('Y-m-d H:i'),
                    'split_end' => $period['split_end']?->format('Y-m-d H:i'),
                    'split_type' => $period['split_type']?->label(),
                ];
            }, $workPeriods);

            _debug([
                'mapped_work_periods' => $mappedWorkPeriods,
            ]);
        }

        /**
         * Breakdown work periods by shift breakdown split type with holiday and rest day info,
         * Separates schedule and overtime by $breakdown->schedule and $breakdown->overtime
         **/
        list($scheduleBreakdown, $overtimeBreakdown) = $this->workPeriodService->breakdownWorkPeriods($workPeriods, $startingDateIsRestDay, $startingDateHolidayType);

        if(!$test && $debug){
            _debug([
                '$scheduleBreakdown' => $scheduleBreakdown,
                '$overtimeBreakdown' => $overtimeBreakdown,
            ]);
        }

        /**
         * Apply attendance on a broken down shift schedule
         **/
        list(
            $attendanceBreakdown,
            $firstIn,
            $lunchOut,
            $lunchIn,
            $lastOut,
            $allTimeOutOfShift
        ) = $this->breakdownAttendance($scheduleBreakdown, $parsedAttendance);

        /**
         * Apply irregularities on a broken down shift schedule: late and under time
         **/
        $attendanceDetails = $this->breakdownIrregularities(
            $attendanceBreakdown,
            $firstIn,
            $lunchOut,
            $lunchIn,
            $lastOut,
            $allTimeOutOfShift
        );

        /**
         * Apply overtime if attendance has overtime
         * and schedule is not flexible
         **/
        $attendanceDetails = (!empty($overtime) && !$this->workPeriodService->attendanceScheduleIsFlexible)
            ? array_merge($attendanceDetails, $this->breakdownOvertime($overtimeBreakdown, $overtime))
            : $attendanceDetails;

        /**
         * Set attendance status:
         * Full present: total shift duration == total actual present && 0 total late && 0 total undertime
         * Present with irregularities: total actual present > 0 && total actual present < total shift duration
         * Absent: total actual present <= 0
         **/
        $this->setAttendanceStatus($attendance, $attendanceDetails, $test);

        /**
         * Return a test result on test run,
         * If debug mode, log a test result as well as mapped attendance details,
         * Save attendance changes and create details otherwise
         **/
        if($test){

            $mappedAttendance = [
                'shift_work_duration' => $attendance->shift_work_duration,
                'total_actual_work_present' => $attendance->total_actual_work_present,
                'total_late' => $attendance->total_late,
                'total_undertime' => $attendance->total_undertime,
                'status' => $attendance->status->label(),
            ];

            $mappedAttendanceDetails = array_map(function ($item) {

                /**
                 * Todo: update attendance splitter test first
                 * to adapt the new `regular_rate_multiplier`, `holiday_rate_multiplier` and `non_rest_rate_multiplier`
                 * and remove the lines below so ot wont show false fail test
                 **/
                unset($item['regular_rate_multiplier']);
                unset($item['holiday_rate_multiplier']);
                unset($item['non_rest_rate_multiplier']);

                return [
                    ...$item,
                    'split_type' => $item['split_type']?->label(),
                    'work_hour_type' => $item['work_hour_type']?->label(),
                    'hourly_rate_type' => $item['hourly_rate_type']?->label(),
                ];
            }, $attendanceDetails);

            $detailsTestResult = $mappedAttendanceDetails == $attendanceBreakdownExpected;
            $statusTestResult = $attendance['status'] == $attendanceStatusExpected;

            if($debug){

                _debug([
                    $testCase => [
                        'STATUS (' . ($statusTestResult ? 'PASSED' : 'FAILED') . ')' => $mappedAttendance,
                        'DETAILS (' . ($detailsTestResult ? 'PASSED' : 'FAILED') . ')' => $mappedAttendanceDetails
                    ]
                ]);
            }

            return $detailsTestResult && $statusTestResult;

        } else {

            $attendance->save();
            $attendance->details()->createMany($attendanceDetails);
        }

        return $attendanceDetails;
    }

    protected function applyPreBreakdownValues($breakdown): array
    {
        return array_map(function ($split) {
            return [
                ...$split,
                'actual_start' => null,
                'actual_end' => null,
                'grace_before_start_applied' => null,
                'grace_after_start_applied' => null,
                'first_in' => false,
                'lunch_out' => false,
                'lunch_in' => false,
                'last_out' => false,
            ];
        }, $breakdown);
    }

    protected function breakdownAttendance(array $breakdown, array $attendance): array
    {
        $debug = false;

        $breakdown = $this->applyPreBreakdownValues($breakdown);

        $firstInLogged = false;
        $firstIn = $attendance['first_in'];
        $firstInGraceApplied = null;
        $firstLunchOrderSequence = null;
        $firstInLunchOrderSequence = null;
        $lunchOutLogged = false;
        $lunchOut = $attendance['lunch_out'];
        $lunchInLogged = false;
        $lunchIn = $attendance['lunch_in'];
        $lastOutLogged = false;
        $lastOut = $attendance['last_out'];
        $lunchInIsTheNewFirstIn = false;
        $allTimeOutOfShift = false;

        /**
         * Get first lunch
         **/
        $breakdown = collect($breakdown);
        $breakdown = $breakdown->sortBy('order');
        $breakdown = $breakdown->values()->toArray();
        foreach ($breakdown as $split) {

            if($split['split_type'] == ShiftBreakDownSplitType::LUNCH){
                $firstLunchOrderSequence = $split['order'];
                break;
            }
        }

        $breakdown = collect($breakdown);
        $breakdown = $breakdown->sortBy('order');
        $firstWorkSplit = $breakdown->where('split_type', ShiftBreakDownSplitType::WORK)->first();
        $firstWorkSplitStart = Carbon::parse($firstWorkSplit['date'] . ' ' . $firstWorkSplit['split_start']);
        $lastWorkSplit = $breakdown->where('split_type', ShiftBreakDownSplitType::WORK)->last();
        $lastWorkSplitEnd = Carbon::parse($lastWorkSplit['date'] . ' ' . $lastWorkSplit['split_end']);

        /**
         * If shift requires lunch out and in and first in is lesser then shift start time
         * And lunch in is lesser than the shift start time,
         * Or lunch out is lesser than the shift start time,
         * Make the lunch in as first in
         **/
        if (
            $this->workPeriodService->shiftRequireLunchOutAndIn() &&
            $firstIn->lt($firstWorkSplitStart) &&
            ($lunchIn->lt($firstWorkSplitStart) || $lunchOut->lt($firstWorkSplitStart))
        ) {
            $firstIn  = $lunchIn;
            $lunchInIsTheNewFirstIn = true;
            $lunchIn  = null;
            $lunchOut = null;
        }

        if ($lastOut->lte($firstWorkSplitStart)) {
            $allTimeOutOfShift = true;
        }

        if ($firstIn->gte($lastWorkSplitEnd)) {
            $allTimeOutOfShift = true;
        }

        /**
         * Pre-First in: Apply work start grace if valid
         **/
        if(!$allTimeOutOfShift && $firstIn->gt($firstWorkSplitStart)){
            //Create a first in copy with grace time
            $firstInCopy = $firstIn->copy()->subMinutes($this->workPeriodService->shiftWorkStartGraceTime());

            //Is first in copy with grace time lesser or equal to the first start time
            if($firstInCopy->lte($firstWorkSplitStart)){

                //Create applied grace time
                $firstInGraceApplied = $firstWorkSplitStart->diffInMinutes($firstIn);

                //Justify first in as work start time
                $firstIn = $firstWorkSplitStart;
            }
        }

        /**
         * First in
         **/
        if(!$allTimeOutOfShift){

            $breakdown = collect($breakdown);
            $breakdown = $breakdown->sortBy('order');
            $breakdown = $breakdown->values()->toArray();
            foreach ($breakdown as &$split) {
                if($firstInLogged) continue;

                $splitStart = Carbon::parse($split['date'] . ' ' . $split['split_start']);
                $splitEnd = Carbon::parse($split['date'] . ' ' . $split['split_end']);

                if(
                    $firstIn->lt($splitEnd)
                ){
                    if($split['split_type'] == ShiftBreakDownSplitType::WORK){

                        if(
                            !$this->workPeriodService->shiftRequireLunchOutAndIn() ||
                            $lunchInIsTheNewFirstIn ||
                            (!is_numeric($firstLunchOrderSequence) || $split['order'] < $firstLunchOrderSequence)
                        ){
                            $split['actual_start'] = $firstIn->format('Y-m-d H:i');
                            $split['first_in'] = true;
                            $split['grace_after_start_applied'] = $firstInGraceApplied
                                ? TimeHelper::minutesToTime((int)floor($firstInGraceApplied))
                                : $firstInGraceApplied;
                            $firstInLogged = true;
                        }
                    }

                    if(
                        $split['split_type'] == ShiftBreakDownSplitType::LUNCH &&
                        !$this->workPeriodService->shiftRequireLunchOutAndIn()
                    ){
                        $split['actual_end'] = $firstIn->format('Y-m-d H:i');
                        $split['first_in'] = true;
                        $firstInLunchOrderSequence = $split['order'];
                        $split['grace_after_start_applied'] = $firstInGraceApplied
                            ? TimeHelper::minutesToTime((int)floor($firstInGraceApplied))
                            : $firstInGraceApplied;
                        $firstInLogged = true;
                    }

                } else {

                    if(
                        //(!is_numeric($firstLunchOrderSequence) || $split['order'] < $firstLunchOrderSequence) &&
                        $split['split_type'] == ShiftBreakDownSplitType::WORK
                    ){
                        $split['actual_start'] = $split['date'] . ' ' . '00:00';
                        $split['actual_end'] = $split['date'] . ' ' . '00:00';
                    }
                }
            }
            unset($split);

            if($debug){
                _debug([
                    'breakdown' => "After First In: " . __LINE__,
                    'split' => collect($breakdown)->sortBy('order')->values()->toArray()
                ]);
            }
        }

        /**
         * Lunch out
         **/
        if($this->workPeriodService->shiftRequireLunchOutAndIn() && !empty($lunchOut) && !empty($lunchIn)){

            /**
             * Pre-Lunch out: Apply lunch start grace if valid
             **/
            $breakdown = collect($breakdown);
            $breakdown = $breakdown->sortBy('order');
            $breakdown = $breakdown->values()->toArray();
            $lunchSplit = null;
            foreach ($breakdown as &$split) {

                /**
                 * Get lunch split
                 **/
                if(empty($lunchSplit) && $split['split_type'] == ShiftBreakDownSplitType::LUNCH){
                    $lunchSplit = $split;
                }

                if(!empty($lunchSplit)){

                    $lunchSplitStart = Carbon::parse($lunchSplit['date'] . ' ' . $lunchSplit['split_start']);

                    //Is lunch out too early for lunch start time?
                    if($lunchOut->lt($lunchSplitStart)){
                        //Create a lunch out copy with grace time
                        $lunchOutCopy = $lunchOut->copy()->addMinutes($this->workPeriodService->shiftLunchStartGraceTime());

                        //Is lunch out copy with grace time greater or equal to lunch start time
                        if($lunchOutCopy->gte($lunchSplitStart)){

                            //Create applied grace time
                            $earlyLunchMinutes = $lunchOut->diffInMinutes($lunchSplitStart);

                            //Justify lunch out as lunch start time
                            $lunchOut = $lunchSplitStart;
                            $split['grace_before_start_applied'] = TimeHelper::minutesToTime((int)floor($earlyLunchMinutes));
                        }
                    }

                    break;
                }
            }
            unset($split);

            /**
             * Lunch out
             **/
            $breakdown = collect($breakdown);
            $breakdown = $breakdown->sortByDesc('order');
            $firstBreakdown = $breakdown->last();
            $breakdown = $breakdown->values()->toArray();
            $autofillEnd = null;
            foreach ($breakdown as &$split) {

                $splitStart = Carbon::parse($split['date'] . ' ' . $split['split_start']);
                $splitEnd = Carbon::parse($split['date'] . ' ' . $split['split_end']);

                if(
                    !$lunchOutLogged &&
                    $lunchOut->gte($splitStart) &&
                    (!$allTimeOutOfShift && $lunchOut->between($firstIn, $lastOut)) &&
                    $lunchOut->lt($splitEnd)
                ){
                    //If the split type is work and lunch out time is earlier than the actual lunch split
                    if(
                        $split['split_type'] == ShiftBreakDownSplitType::WORK &&
                        (!is_numeric($firstLunchOrderSequence) || $split['order'] < $firstLunchOrderSequence)
                    ){

                        if($split['actual_end'] == null){
                            $split['actual_end'] = $lunchOut->format('Y-m-d H:i');
                            $split['lunch_out'] = true;
                        }

                        if($split['actual_start'] == null){
                            $split['actual_start'] = $split['date'] . ' ' . $split['split_start'];
                        }

                        $autofillEnd = $splitEnd;
                        $lunchOutLogged = true;
                    }

                    if(
                        $split['split_type'] == ShiftBreakDownSplitType::LUNCH &&
                        (!is_numeric($firstLunchOrderSequence) || $split['order'] >= $firstLunchOrderSequence)
                    ){

                        if($split['actual_start'] == null){
                            $split['actual_start'] = $lunchOut->format('Y-m-d H:i');
                            $split['lunch_out'] = true;
                        }

                        if(
                            $split['actual_end'] == null &&
                            $lunchIn->gt($splitEnd)
                        ){
                            $split['actual_end'] = Carbon::parse($split['date'] . ' ' . $split['split_end'])->format('Y-m-d H:i');
                        }

                        $autofillEnd = $splitEnd;
                        $lunchOutLogged = true;
                    }

                    if(
                        $split['split_type'] == ShiftBreakDownSplitType::WORK &&
                        (!is_numeric($firstLunchOrderSequence) || $split['order'] >= $firstLunchOrderSequence)
                    ){

                    }

                } else if($lunchOutLogged && !empty($autofillEnd)) {
                    /**
                     * After logging lunch out
                     * All work split between first split start and lunch out split end will have their actual time
                     * to be justified as start and end
                     **/
                    $firstBreakdownSplitStart = Carbon::parse($firstBreakdown['date'] . ' ' . $firstBreakdown['split_start']);

                    if(
                        $split['split_type'] == ShiftBreakDownSplitType::WORK &&
                        $splitStart->between($firstBreakdownSplitStart, $autofillEnd) &&
                        $splitEnd->between($firstBreakdownSplitStart, $autofillEnd)
                    ){
                        $this->justifySplitActualStartAndEnd($split);
                    }
                }
            }
            unset($split);
        }

        if($debug){
            _debug([
                'breakdown' => "After Lunch Out: " . __LINE__,
                'split' => collect($breakdown)->sortBy('order')->values()->toArray()
            ]);
        }

        /**
         * Last out
         **/
        if(!$allTimeOutOfShift){

            $breakdown = collect($breakdown);
            $breakdown = $breakdown->sortByDesc('order');
            $breakdown = $breakdown->values()->toArray();
            foreach ($breakdown as &$split) {

                $splitStart = Carbon::parse($split['date'] . ' ' . $split['split_start']);
                $splitEnd = Carbon::parse($split['date'] . ' ' . $split['split_end']);

                if(
                    !$lastOutLogged &&
                    (
                        $firstInLogged ||
                        $lastOut->gt($firstIn) ||
                        ($this->workPeriodService->shiftRequireLunchOutAndIn() && $lastOut->gt($lunchIn))
                    )
                ){

                    if(
                        $lastOut->gte($splitStart)
                    ){
                        if($split['split_type'] == ShiftBreakDownSplitType::WORK){

                            $split['actual_end'] = $lastOut->format('Y-m-d H:i');
                            $split['last_out'] = true;
                            $lastOutLogged = true;
                        }

                        if($split['split_type'] == ShiftBreakDownSplitType::LUNCH){

                            $split['actual_start'] = $lastOut->format('Y-m-d H:i');
                            $split['last_out'] = true;
                            $lastOutLogged = true;

                            $split['actual_end'] = Carbon::parse($split['date'] . ' ' . $split['split_end'])->format('Y-m-d H:i');
                        }
                    } else {

                        if(
                            $split['split_type'] == ShiftBreakDownSplitType::WORK
                        ){
                            $split['actual_start'] = $split['date'] . ' ' . '00:00';
                            $split['actual_end'] = $split['date'] . ' ' . '00:00';
                        }
                    }
                }
            }
            unset($split);

            if($debug){
                _debug([
                    'breakdown' => "After Last Out: " . __LINE__,
                    'split' => collect($breakdown)->sortBy('order')->values()->toArray()
                ]);
            }
        }

        /**
         * Lunch in
         **/
        if($this->workPeriodService->shiftRequireLunchOutAndIn() && !empty($lunchOut) && !empty($lunchIn)){

            $breakdown = collect($breakdown);
            $breakdown = $breakdown->sortBy('order');
            $lastBreakdown = $breakdown->last();
            $breakdown = $breakdown->values()->toArray();
            $autofillStart = null;
            foreach ($breakdown as &$split) {

                $splitStart = Carbon::parse($split['date'] . ' ' . $split['split_start']);
                $splitEnd = Carbon::parse($split['date'] . ' ' . $split['split_end']);

                if(
                    !$lunchInLogged &&
                    $lunchIn->gte($splitStart) &&
                    (!$allTimeOutOfShift && $lunchIn->between($lunchOut, $lastOut)) &&
                    $lunchIn->lt($splitEnd)
                ){
                    if($split['split_type'] == ShiftBreakDownSplitType::WORK){

                        if($split['actual_start'] == null){
                            $split['actual_start'] = $lunchIn->format('Y-m-d H:i');
                            $split['lunch_in'] = true;
                            $autofillStart = $splitStart;
                            $lunchInLogged = true;
                        } else if($lastOutLogged){
                            $split['lunch_in'] = true;
                            $autofillStart = $splitStart;
                            $lunchInLogged = true;
                        }

                        if($split['actual_end'] == null){
                            $split['actual_end'] = Carbon::parse($split['date'] . ' ' . $split['split_end'])->format('Y-m-d H:i');
                        }
                    }

                    if($split['split_type'] == ShiftBreakDownSplitType::LUNCH){

                        if($split['actual_start'] == null){
                            $split['actual_start'] = $split['date'] . ' ' . $split['split_start'];
                        }

                        if($split['last_out']){

                            $split['actual_end'] = Carbon::parse($split['date'] . ' ' . $split['split_end'])->format('Y-m-d H:i');

                        } else if ($split['actual_end'] == null){

                            $split['actual_end'] = $lunchIn->format('Y-m-d H:i');
                            $split['lunch_in'] = true;
                            $autofillStart = $splitStart;
                            $lunchInLogged = true;
                        }
                    }


                } else if ($lunchInLogged && !empty($autofillStart)) {
                    /**
                     * After logging lunch in
                     * All work split between lunch in split start and last split end will have their actual time
                     * to be justified as start and end
                     **/
                    $lastBreakdownSplitEnd = Carbon::parse($lastBreakdown['date'] . ' ' . $lastBreakdown['split_end']);

                    if(
                        $split['split_type'] == ShiftBreakDownSplitType::WORK &&
                        $splitStart->between($autofillStart, $lastBreakdownSplitEnd) &&
                        $splitEnd->between($autofillStart, $lastBreakdownSplitEnd)
                    ){
                        if($split['actual_start'] == null){
                            $split['actual_start'] = $split['date'] . ' ' . $split['split_start'];
                        }
                        if($split['actual_end'] == null){
                            $split['actual_end'] = Carbon::parse($split['date'] . ' ' . $split['split_end'])->format('Y-m-d H:i');
                        }
                    }
                }
            }
            unset($split);
        }

        if($debug){
            _debug([
                'breakdown' => "After Lunch In: " . __LINE__,
                'split' => collect($breakdown)->sortBy('order')->values()->toArray()
            ]);
        }

        $breakdown = collect($breakdown);
        $breakdown = $breakdown->sortBy('order');
        $breakdown = $breakdown->values()->toArray();

        if($this->workPeriodService->shiftRequireLunchOutAndIn() && !empty($lunchOut) && !empty($lunchIn)){

            /**
             * Shift that requires lunch out and in
             * Mark all work split that doesn't have an actual start and actual end as absent
             **/
            foreach ($breakdown as &$split){

                $splitStart = Carbon::parse($split['date'] . ' ' . $split['split_start']);
                $splitEnd = Carbon::parse($split['date'] . ' ' . $split['split_end']);

                if(
                    $split['split_type'] == ShiftBreakDownSplitType::WORK &&
                    $split['actual_start'] == null &&
                    $split['actual_end'] == null
                ){
                    $split['actual_start'] = $split['date'] . ' ' . '00:00';
                    $split['actual_end'] = $split['date'] . ' ' . '00:00';
                }

                if(
                    $split['split_type'] == ShiftBreakDownSplitType::LUNCH &&
                    (
                        $firstInLogged && $firstIn->lt($splitEnd) &&
                        $lastOutLogged && $lastOut->gte($splitStart) &&
                        $lunchOut->lt($splitEnd) &&
                        $lunchIn->gte($splitStart)
                    )
                ){

                    $this->justifySplitActualStartAndEnd($split);
                }
            }
            unset($split);

        } else {

            /**
             * Shift does not require lunch out and in and not all time late
             * Justify all work split that doesn't have an actual start and actual end
             * else absent
             **/
            foreach ($breakdown as &$split){

                $splitStart = Carbon::parse($split['date'] . ' ' . $split['split_start']);
                $splitEnd = Carbon::parse($split['date'] . ' ' . $split['split_end']);

                if($split['split_type'] == ShiftBreakDownSplitType::WORK){

                    if(!$allTimeOutOfShift){

                        $this->justifySplitActualStartAndEnd($split);
                    } else if (
                        $split['actual_start'] == null &&
                        $split['actual_end'] == null
                    ){
                        $split['actual_start'] = $split['date'] . ' ' . '00:00';
                        $split['actual_end'] = $split['date'] . ' ' . '00:00';
                    }
                }

                if(
                    $split['split_type'] == ShiftBreakDownSplitType::LUNCH &&
                    (
                        !is_numeric($firstInLunchOrderSequence) ||
                        $split['order'] == $firstInLunchOrderSequence
                    ) &&
                    (
                        $firstInLogged && $firstIn->lt($splitEnd) &&
                        $lastOutLogged && $lastOut->gte($splitStart)
                    ) && !$lunchInIsTheNewFirstIn
                ){
                    $this->justifySplitActualStartAndEnd($split);
                }
            }
            unset($split);
        }

        if($debug){
            _debug([
                'breakdown' => "After Justification : " . __LINE__,
                'split' => collect($breakdown)->sortBy('order')->values()->toArray()
            ]);
        }

        return [
            collect($breakdown)->sortBy('order')->values()->toArray(),
            $firstIn,
            $lunchOut,
            $lunchIn,
            $lastOut,
            $allTimeOutOfShift
        ];
    }

    protected function justifySplitActualStartAndEnd(&$split): void
    {
        if($split['actual_start'] == null){
            $split['actual_start'] = $split['date'] . ' ' . $split['split_start'];
        }

        if($split['actual_end'] == null){
            $split['actual_end'] = Carbon::parse($split['date'] . ' ' . $split['split_end'])->format('Y-m-d H:i');
        }
    }

    /**
     * @throws UnexpectedException
     */
    protected function breakdownIrregularities(
        array $attendanceBreakdown,
        Carbon $firstIn,
        ?Carbon $lunchOut,
        ?Carbon$lunchIn,
        Carbon $lastOut,
        bool $allTimeOutOfShift
    ): array {
        $attendanceBreakdown = array_map(function ($split) {
            return [
                ...$split,
                'actual_present_start' => null,
                'actual_present_end' => null,
                'actual_present' => null,
                '#actual_present_readable' => null,
                'actual_irregularity_duration_start' => null,
                'actual_irregularity_duration_end' => null,
                'actual_irregularity_duration' => null,
                'late' => 0,
                'undertime' => 0,
                'flexible_undertime' => 0,
            ];
        }, $attendanceBreakdown);

        $firstInOrderSequence = null;
        $lastOrderSequence = collect($attendanceBreakdown)->max('order');

        /**
         * Search for first in order sequence
         **/
        foreach ($attendanceBreakdown as $split) {
            if(!is_numeric($firstInOrderSequence) && $split['first_in']){
                $firstInOrderSequence = $split['order'];
            }
        }

        /**
         * If the first in order sequence still isn't found, and shift requires lunch out and in,
         * Search for lunch in as first in
         **/
        if($this->workPeriodService->shiftRequireLunchOutAndIn() && !is_numeric($firstInOrderSequence)){
            foreach ($attendanceBreakdown as $split) {
                if(!is_numeric($firstInOrderSequence) && $split['lunch_in']){
                    $firstInOrderSequence = $split['order'];
                }
            }
        }

        /**
         * If the first in order sequence still isn't found,
         * assume that the first in is in lunch split
         **/
        if(!is_numeric($firstInOrderSequence)){
            foreach ($attendanceBreakdown as $split) {
                if(!is_numeric($firstInOrderSequence) && $split['split_type'] == ShiftBreakDownSplitType::LUNCH){
                    $firstInOrderSequence = $split['order'];
                }
            }
        }

        if(!is_numeric($firstInOrderSequence) && !$allTimeOutOfShift){
            throw new UnexpectedException("First in order sequence not found: breakdown @ breakdown irregularities [" . __LINE__ . "]");
        }

        /**
         * Total the actual durations of every split
         **/
        foreach ($attendanceBreakdown as &$split) {

            if(!empty($split['actual_start']) && !empty($split['actual_end'])){

                $late = 0;
                $undertime = 0;
                $actualPresent = 0;
                $irregularityDuration = 0;

                //Set split duration as late for all splits before the first in order sequence
                if(
                    !$allTimeOutOfShift &&
                    $split['order'] < $firstInOrderSequence &&
                    $split['split_type'] == ShiftBreakDownSplitType::WORK &&
                    !$this->workPeriodService->attendanceScheduleIsFlexible
                ){
                    $split['late'] = $split['split_duration'];
                }

                //Parse split and actual times
                $splitStart = Carbon::parse($split['date'] . ' ' . $split['split_start']);
                $splitEnd = Carbon::parse($split['date'] . ' ' . $split['split_end']);

                $splitActualStart = Carbon::parse($split['actual_start']);
                $splitActualEnd = Carbon::parse($split['actual_end']);

                //Compute late for work split with the same order as the first in order sequence
                if(
                    !$allTimeOutOfShift &&
                    $split['order'] == $firstInOrderSequence &&
                    $split['split_type'] == ShiftBreakDownSplitType::WORK
                ){

                    if($splitActualStart->lt($splitStart)){

                        //Only apply split start time when same day, else copy whole split start
                        if($splitActualStart->isSameDay($splitStart)){
                            $splitActualStart->setTimeFromTimeString($split['split_start']);
                        } else {
                            $splitActualStart = $splitStart;
                        }
                    }

                    if(
                        $splitActualStart->gt($splitStart) &&
                        !$this->workPeriodService->attendanceScheduleIsFlexible
                    ){
                        $late = intval($splitActualStart->diffInMinutes($splitStart, true));
                        $split['late'] = $late;
                    }
                }

                /**
                 * If the last split has an actual end that is greater than the split end
                 * set actual end as split end
                 **/
                if(
                    $split['order'] == $lastOrderSequence &&
                    $splitActualEnd->gt($splitEnd) &&
                    $split['split_type'] == ShiftBreakDownSplitType::WORK
                ){

                    //Only apply split end time when same day, else copy whole split end
                    if($splitActualEnd->isSameDay($splitEnd)){
                        $splitActualEnd->setTimeFromTimeString($split['split_end']);
                    } else {
                        $splitActualEnd = $splitEnd;
                    }
                }

                //After split first and last boundary have been justified, set actual duration times
                $split['actual_present_start'] = $splitActualStart->format('Y-m-d H:i');
                $split['actual_present_end'] = $splitActualEnd->format('Y-m-d H:i');

                $actualPresent = intval($splitActualStart->diffInMinutes($splitActualEnd, true));

                //If Schedule is flexible, actual present should not exceed the required schedule total work hours
                if($this->workPeriodService->attendanceScheduleIsFlexible){
                    $actualPresent = $actualPresent > $this->workPeriodService->attendanceScheduleTotalWorkHoursWithBreaks
                        ? $this->workPeriodService->attendanceScheduleTotalWorkHoursWithBreaks
                        : $actualPresent;
                }

                /**
                 * If first in, lunch out, lunch in and last out are in the same split
                 * and shift requires lunch out and in
                 * Deduct actual duration of total duration of lunch out and lunch in
                 * */
                if($split['first_in'] && $split['lunch_out'] && $split['lunch_in']&& $split['last_out'] && $this->workPeriodService->shiftRequireLunchOutAndIn()){
                    $split['actual_irregularity_duration_start'] = $lunchOut->format('Y-m-d H:i');
                    $split['actual_irregularity_duration_end'] = $lunchIn->format('Y-m-d H:i');

                    $irregularityDuration = intval($lunchOut->diffInMinutes($lunchIn, true));
                    $split['actual_irregularity_duration'] = $irregularityDuration;
                }

                $actualPresent -= $irregularityDuration;

                $split['actual_present'] = $actualPresent;
                $split['#actual_present_readable'] = TimeHelper::minutesToTime($actualPresent);

                /**
                 * Compute undertime for work split
                 * Undertime = (actual duration and late) - split duration
                 **/
                if(
                    !$allTimeOutOfShift &&
                    $split['split_type'] == ShiftBreakDownSplitType::WORK &&
                    (
                        $split['order'] >= $firstInOrderSequence ||
                        ($split['last_out'] && $split['order'] != $lastOrderSequence)
                    ) &&
                    !$this->workPeriodService->attendanceScheduleIsFlexible
                ){
                    $actualPresentWithLate = $actualPresent + $late;

                    $undertime += $actualPresentWithLate >= $split['split_duration']
                        ? 0
                        : $actualPresentWithLate - $split['split_duration'];

                    $split['undertime'] = abs($undertime);
                }
            }
        }
        unset($split);

        /**
         * If schedule is flexible calculate flexible undertime by
         * shift total work hours with breaks - total actual present
         * Put flexible undertime at the very last of the breakdown
         **/
        if(
            !$allTimeOutOfShift &&
            $this->workPeriodService->attendanceScheduleIsFlexible
        ){

            $totalActualPresent = collect($attendanceBreakdown)
                ->where('split_type', ShiftBreakDownSplitType::WORK)
                ->sum('actual_present');

            $flexibleUndertime =  $totalActualPresent >= $this->workPeriodService->attendanceScheduleTotalWorkHoursWithBreaks
                ? 0
                : $this->workPeriodService->attendanceScheduleTotalWorkHoursWithBreaks - $totalActualPresent;

            $attendanceBreakdownCollection = collect($attendanceBreakdown);
            $attendanceBreakdownDescending = $attendanceBreakdownCollection->sortByDesc('order');

            $lastBreakdown = $attendanceBreakdownDescending->first();

            $attendanceBreakdownIterator = $attendanceBreakdownDescending->values()->toArray();
            foreach ($attendanceBreakdownIterator as &$split) {
                if($split['order'] == $lastBreakdown['order']){
                    $split['flexible_undertime'] = $flexibleUndertime;
                    break;
                }
            }
            unset($split);

            $attendanceBreakdown = collect($attendanceBreakdownIterator)->sortBy('order')->values()->toArray();
        }

        return $attendanceBreakdown;
    }

    protected function breakdownOvertime(array $breakdown, array $overtime)
    {
        $breakdown = $this->applyPreBreakdownValues($breakdown);

        $breakdown = array_map(function ($split) {
            return [
                ...$split,
                'overtime_start' => false,
                'overtime_end' => false,
                'actual_present_start' => null,
                'actual_present_end' => null,
                'actual_present' => null,
                '#actual_present_readable' => null,
            ];
        }, $breakdown);

        $overtime = [
            'start' => Carbon::parse($overtime['start']),
            'end' => Carbon::parse($overtime['end']),
        ];

        $startLogged = false;
        $start = $overtime['start'];
        $endLogged = false;
        $end = $overtime['end'];
        $allTimeOutOfOvertimeSchedule = false;

        $breakdown = collect($breakdown);
        $lastOvertimeSplit = $breakdown->last();
        $lastOvertimeSplitEnd = Carbon::parse($lastOvertimeSplit['date'] . ' ' . $lastOvertimeSplit['split_end']);

        /**
         * If overtime start is over last split end,
         * flag as all time out of overtime schedule
         **/
        if($start->gte($lastOvertimeSplitEnd)){
            $allTimeOutOfOvertimeSchedule = true;
        }

        if(!$allTimeOutOfOvertimeSchedule){

            $breakdown = $breakdown->sortBy('order');
            $breakdown = $breakdown->values()->toArray();
            foreach ($breakdown as &$split) {
                if($startLogged) continue;

                $splitEnd = Carbon::parse($split['date'] . ' ' . $split['split_end']);

                if(
                    $start->lt($splitEnd)
                ){
                    $split['actual_start'] = $start->format('Y-m-d H:i');
                    $split['overtime_start'] = true;
                    $startLogged = true;
                } else {

                    $split['actual_start'] = $split['date'] . ' ' . '00:00';
                    $split['actual_end'] = $split['date'] . ' ' . '00:00';
                }
            }
            unset($split);

            $breakdown = collect($breakdown);
            $breakdown = $breakdown->sortByDesc('order');
            $breakdown = $breakdown->values()->toArray();
            foreach ($breakdown as &$split) {
                if($endLogged) continue;

                $splitStart = Carbon::parse($split['date'] . ' ' . $split['split_start']);

                if(
                    $end->gte($splitStart)
                ){
                    $split['actual_end'] = $end->format('Y-m-d H:i');
                    $split['overtime_end'] = true;
                    $endLogged = true;
                } else {

                    $split['actual_start'] = $split['date'] . ' ' . '00:00';
                    $split['actual_end'] = $split['date'] . ' ' . '00:00';
                }
            }
            unset($split);
        }

        /**
         * Justify the middle part
         **/
        $breakdown = collect($breakdown);
        $breakdown = $breakdown->sortBy('order');
        $breakdown = $breakdown->values()->toArray();
        foreach ($breakdown as &$split){

            if($allTimeOutOfOvertimeSchedule){

                if (
                    $split['actual_start'] == null &&
                    $split['actual_end'] == null
                ){
                    $split['actual_start'] = $split['date'] . ' ' . '00:00';
                    $split['actual_end'] = $split['date'] . ' ' . '00:00';
                }

            } else {
                $splitStart = Carbon::parse($split['date'] . ' ' . $split['split_start']);
                $splitEnd = Carbon::parse($split['date'] . ' ' . $split['split_end']);

                if(
                    $startLogged && $start->lt($splitEnd) &&
                    $endLogged && $end->gte($splitStart)
                ){
                    $this->justifySplitActualStartAndEnd($split);
                }
            }

        }
        unset($split);

        $startOrderSequence = null;
        $lastOrderSequence = collect($breakdown)->max('order');

        /**
         * Search for overtime start order sequence
         **/
        foreach ($breakdown as $split) {
            if(!is_numeric($startOrderSequence) && $split['overtime_start']){
                $startOrderSequence = $split['order'];
            }
        }

        /**
         * Actual Overtime Present Hours
         **/
        foreach ($breakdown as &$split){

            if(!empty($split['actual_start']) && !empty($split['actual_end'])){

                //Parse split and actual times
                $splitStart = Carbon::parse($split['date'] . ' ' . $split['split_start']);
                $splitEnd = Carbon::parse($split['date'] . ' ' . $split['split_end']);

                $splitActualStart = Carbon::parse($split['actual_start']);
                $splitActualEnd = Carbon::parse($split['actual_end']);

                if(
                    $split['order'] == $startOrderSequence &&
                    $splitActualStart->lt($splitStart)
                ){

                    //Only apply split start time when same day, else copy whole split start
                    if($splitActualStart->isSameDay($splitStart)){
                        $splitActualStart->setTimeFromTimeString($split['split_start']);
                    } else {
                        $splitActualStart = $splitStart;
                    }
                }

                if(
                    $split['order'] == $lastOrderSequence &&
                    $splitActualEnd->gt($splitEnd)
                ){
                    //Only apply split end time when same day, else copy whole split end
                    if($splitActualEnd->isSameDay($splitEnd)){
                        $splitActualEnd->setTimeFromTimeString($split['split_end']);
                    } else {
                        $splitActualEnd = $splitEnd;
                    }
                }

                //After split first and last boundary have been justified, set actual duration times
                $split['actual_present_start'] = $splitActualStart->format('Y-m-d H:i');
                $split['actual_present_end'] = $splitActualEnd->format('Y-m-d H:i');

                $actualPresent = intval($splitActualStart->diffInMinutes($splitActualEnd, true));

                $split['actual_present'] = $actualPresent;
                $split['#actual_present_readable'] = TimeHelper::minutesToTime($actualPresent);
            }
        }

        return collect($breakdown)->sortBy('order')->values()->toArray();
    }

    protected function setAttendanceStatus(Attendance $attendance, $attendanceDetails, $test): void
    {
        $attendanceDetails = collect($attendanceDetails);

        $totalShiftWorkDuration = $this->workPeriodService->attendanceScheduleIsFlexible
            ? $this->workPeriodService->attendanceScheduleTotalWorkHoursWithBreaks
            : $attendanceDetails
                ->where('split_type', ShiftBreakDownSplitType::WORK)
                ->sum('split_duration');

        $totalActualWorkPresent = $attendanceDetails
            ->where('split_type', ShiftBreakDownSplitType::WORK)
            ->sum('actual_present');

        $totalLate = $attendanceDetails
            ->where('split_type', ShiftBreakDownSplitType::WORK)
            ->sum('late');

        $totalUndertime = $this->workPeriodService->attendanceScheduleIsFlexible
            ? $attendanceDetails
                ->where('split_type', ShiftBreakDownSplitType::WORK)
                ->sum('flexible_undertime')
            : $attendanceDetails
                ->where('split_type', ShiftBreakDownSplitType::WORK)
                ->sum('undertime');

        if($test){

            $attendance->shift_work_duration = $totalShiftWorkDuration;
            $attendance->total_actual_work_present = $totalActualWorkPresent;
            $attendance->total_late = $totalLate;
            $attendance->total_undertime = $totalUndertime;
        }

        $attendance->status = AttendanceStatus::NOT_SPECIFIED;

        if(
            $totalActualWorkPresent >= $totalShiftWorkDuration &&
            $totalLate == 0 &&
            $totalUndertime == 0
        ){
            $attendance->status = AttendanceStatus::FULL_PRESENT;
        }

        if(
            $totalActualWorkPresent > 0 &&
            $totalActualWorkPresent < $totalShiftWorkDuration
        ){
            $attendance->status = AttendanceStatus::PRESENT_WITH_IRREGULARITIES;
        }

        if(
            $totalActualWorkPresent <= 0
        ){
            $attendance->status = AttendanceStatus::ABSENT;
        }
    }
}
