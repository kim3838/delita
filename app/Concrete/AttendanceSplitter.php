<?php

namespace App\Concrete;

use App\Blueprint\AttendanceSplitterInterface;
use App\Enums\HolidayType;
use App\Enums\HourlyRateType;
use App\Enums\ShiftBreakDownSplitType;
use App\Enums\WorkHourType;
use App\Exceptions\NotFoundException;
use App\Helpers\TimeHelper;
use App\Models\Company;
use App\Models\Shift;
use App\Traits\WorkPeriod;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class AttendanceSplitter implements AttendanceSplitterInterface
{
    //Set on construct
    protected string $nightStart = '22:00';
    //Set on construct
    protected string $nightEnd = '06:00';
    //Set on generate: attendance shift
    protected ?Shift $shift = null;
    //Set on generate: shift work_start_grace_time
    protected int $shiftWorkStartGraceTime = 0;
    //Set on generate: shift require_lunch_time_in_and_out
    protected bool $shiftRequireLunchOutAndIn = false;
    //Set on generate: shift lunch_start_grace_time
    protected int $shiftLunchStartGraceTime = 0;
    //Set on generate: shift max_overtime
    protected float $shiftOvertimeLimit = 0;
    //Set on generate: shift schedules rest days
    protected array $restDays = [];
    //Set on generate: shift schedules
    protected array $schedules = [];
    //Set on generate: Schedule of the same week day as the attendance date
    protected ?array $attendanceSchedule = null;
    //Set on generate: Has lunch break from attendance schedule
    protected ?bool $attendanceScheduleHasLunchBreak = false;
    //Set on generate: Is day off from attendance schedule
    protected ?bool $attendanceScheduleIsDayOff = false;
    //Set on generate: Is flexible from attendance schedule
    protected ?bool $attendanceScheduleIsFlexible = false;
    //Set on generate: Total work hours with breaks from attendance schedule
    protected ?int $attendanceScheduleTotalWorkHoursWithBreaks = 0;
    //Set on construct: holidays
    protected array $holidays = [];

    use WorkPeriod;

    public function __construct(
        protected readonly ?Company $company,
    ){
        //Set company holidays
        $this->holidays = [
            [
                'date' => '2025-08-02',
                'type' => HolidayType::SPECIAL
            ]
        ];

        //Set company formula settings
        $this->resolveCompanyFormulaSettings();

        //Set company night hours
        $this->resolveCompanyNightHoursFromBasicSalaryFormulaSettings();
    }

    /**
     * Split attendance by night hours, midnight and lunch
     * Calculate late and undertime
     *
     * @throws NotFoundException
     */
    public function generate(array $attendance, $test = false, $debug = false): bool | array
    {
        /**
         * Set attendance shift
         **/
        $this->setShift($attendance['shift_id']);

        $testCase = null;
        $testScenario = null;
        $attendanceBreakdownExpected = null;

        if($test){
            $testCase = $attendance['test_case'] ?? null;
            $testScenario = $attendance['scenario'] ?? null;

            $attendanceBreakdownExpected = $attendance['expected'] ?? null;
        }

        /**
         * Attendance date
         **/
        $date = Carbon::parse($attendance['date']);

        /**
         * Set a schedule of the same week day as the attendance date
         * Set schedule has lunch break
         * Set schedule is day off
         * Set schedule is flexible
         **/
        $this->setAttendanceSchedule($date);

        $schedule = $this->attendanceSchedule;

        if($this->attendanceScheduleIsDayOff){
            return [];
        }

        /**
         * Parse schedule times
         **/
        $schedule = $this->parseSchedule($schedule, $date);

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
        $attendance = $this->parseAttendance($attendance);

        if(!$test && $debug){

            $mappedAttendance = [
                'first_in' => $attendance['first_in']?->format('Y-m-d H:i'),
                'lunch_out' => $attendance['lunch_out']?->format('Y-m-d H:i'),
                'lunch_in' => $attendance['lunch_in']?->format('Y-m-d H:i'),
                'last_out' => $attendance['last_out']?->format('Y-m-d H:i'),
            ];

            _debug([
                '$attendance' => $mappedAttendance
            ]);

        }

        /**
         * Calculate work periods
         **/
        $workPeriods = $this->calculateWorkPeriods($schedule);

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
        $breakdown = $this->breakdownWorkPeriods($workPeriods);

        /**
         * Apply attendance on a broken down shift schedule
         **/
        $attendance = $this->breakdownAttendance($breakdown->schedule, $attendance);

        /**
         * Apply irregularities on a broken down shift schedule: late and under time
         **/
        $attendance = $this->breakdownIrregularities($attendance);

        if($test){

            $mappedAttendanceIrregularities = array_map(function ($item) {
                return [
                    ...$item,
                    'split_type' => $item['split_type']?->label(),
                    'work_hour_type' => $item['work_hour_type']?->label(),
                    'hourly_rate_type' => $item['hourly_rate_type']?->label(),
                ];
            }, $attendance);

            $testResult = $mappedAttendanceIrregularities == $attendanceBreakdownExpected;

            if($debug){

                _debug([
                    $testScenario . ' (' . ($testResult ? 'PASSED' : 'FAILED') . ')'  => $mappedAttendanceIrregularities,
                ]);
            }

            return $testResult;
        }

        return $attendance;
    }

    protected function parseSchedule(array $schedule, Carbon $date): array
    {
        // Work start
        $workStart = $date->copy()->setTimeFromTimeString($schedule['work_start']);

        // Lunch start
        $lunchStart = (isset($schedule['lunch_break_start']) && !$this->attendanceScheduleIsFlexible)
            ? $date->copy()->setTimeFromTimeString($schedule['lunch_break_start'])
            : null;

        if(!empty($lunchStart) && $lunchStart->lt($workStart)){
            $lunchStart->addDay();
        }

        // Lunch end
        $lunchEnd = (isset($schedule['lunch_break_end']) && !$this->attendanceScheduleIsFlexible)
            ? $date->copy()->setTimeFromTimeString($schedule['lunch_break_end'])
            : null;

        if(!empty($lunchStart) && !empty($lunchEnd) && $lunchEnd->lt($lunchStart)){
            $lunchEnd->addDay();
        }

        // Work end
        $workEnd = $date->copy()->setTimeFromTimeString($schedule['work_end']);

        if($workEnd->lte($workStart)){
            $workEnd->addDay();
        }

        //Overtime end
        $overtimeEnd = (!$this->attendanceScheduleIsFlexible && $this->shiftOvertimeLimit > 0)
            ? $workEnd->copy()->addHours($this->shiftOvertimeLimit)
            : null;

        return [
            'work_start' => $workStart,
            'lunch_start' => $lunchStart,
            'lunch_end' => $lunchEnd,
            'work_end' => $workEnd,
            ...(!empty($overtimeEnd) ? ['overtime_end' => $overtimeEnd] : [])
        ];
    }

    protected function parseAttendance(array $attendanceData): array
    {
        return [
            'first_in' => Carbon::parse($attendanceData['first_in']),
            'lunch_out' => $attendanceData['lunch_out'] ? Carbon::parse($attendanceData['lunch_out']) : null,
            'lunch_in' => $attendanceData['lunch_in'] ? Carbon::parse($attendanceData['lunch_in']) : null,
            'last_out' => Carbon::parse($attendanceData['last_out'])
        ];
    }

    protected function getNextDayIfNeeded(Carbon $date, string $time, Carbon $reference): Carbon
    {
        $carbon = $date->copy()->setTimeFromTimeString($time);

        // If the time is before the reference time, assume it's the next day
        if ($carbon->lte($reference)) {
            $carbon->addDay();
        }

        return $carbon;
    }

    protected function calculateWorkPeriods(array $schedule): array
    {
        $periods = [];

        // First work period: work_start to lunch_start (or work_end if no lunch)
        $firstPeriodStart = $schedule['work_start'];
        $firstPeriodEnd = $schedule['lunch_start'] ?? $schedule['work_end'];
        $overtimeEnd = $schedule['overtime_end'] ?? null;

        if ($firstPeriodStart && $firstPeriodEnd) {
            $periods[] = [
                'split_type' => ShiftBreakDownSplitType::WORK,
                'split_start' => $firstPeriodStart,
                'split_end' => $firstPeriodEnd
            ];
        }

        // Lunch period (no work)
        if ($schedule['lunch_start'] && $schedule['lunch_end']) {
            $periods[] = [
                'split_type' => ShiftBreakDownSplitType::LUNCH,
                'split_start' => $schedule['lunch_start'],
                'split_end' => $schedule['lunch_end']
            ];
        }

        // Second work period: lunch_end to work_end
        if ($schedule['lunch_end'] && $schedule['work_end']) {
            $periods[] = [
                'split_type' => ShiftBreakDownSplitType::WORK,
                'split_start' => $schedule['lunch_end'],
                'split_end' => $schedule['work_end']
            ];
        }

        //Overtime
        if($schedule['work_end'] && $overtimeEnd){
            $periods[] = [
                'split_type' => ShiftBreakDownSplitType::OVERTIME,
                'split_start' => $schedule['work_end'],
                'split_end' => $overtimeEnd
            ];
        }

        return $periods;
    }

    protected function breakdownWorkPeriods(array $periods): object
    {
        $breakdownSequence = 1;
        $schedule = [];
        $overtime = [];

        foreach ($periods as $period) {

            // Split work periods into hourly segments and categorize
            $categorizedSplit = $this->categorizeWorkPeriod($period['split_start'], $period['split_end'], $period['split_type'], $breakdownSequence);

            if(in_array($period['split_type'], [ShiftBreakDownSplitType::WORK, ShiftBreakDownSplitType::LUNCH]) && !empty($categorizedSplit)){

                $schedule = array_merge($schedule, $categorizedSplit);
            }

            if($period['split_type'] == ShiftBreakDownSplitType::OVERTIME && !empty($categorizedSplit)){

                $overtime = array_merge($overtime, $categorizedSplit);
            }
        }

        return (object)[
            'schedule' => $schedule,
            'overtime' => $overtime,
        ];
    }

    protected function categorizeWorkPeriod(Carbon $startTime, Carbon $endTime, ShiftBreakDownSplitType $splitType, &$breakdownSequence): array
    {
        $breakdown = [];
        $current = $startTime->copy();

        while ($current->lessThan($endTime)) {

            // Determine the next boundary (regular/night transition or midnight)
            $nextBoundary = $this->getNextTimeBoundary($current, $endTime);

            $duration = intval($current->diffInMinutes($nextBoundary, true));

            // Determine work hour type
            $workHourType = $this->getWorkHourType($current);

            // Determine rate type
            $hourlyRateType = $this->getHourlyRate($current, $workHourType, $splitType);

            $hourlyRateMultiplier = $this->getHourlyRateMultiplier($workHourType, $hourlyRateType, $splitType);

            $breakdown[] = [
                'date' => $current->format('Y-m-d'),
                'split_type' => $splitType,
                'split_start' => $current->format('H:i'),
                'split_end' => $this->formatEndTime($nextBoundary),
                'split_duration' => $duration,
                '#split_duration_readable' => $this->formatDuration($current, $nextBoundary),
                'work_hour_type' => $workHourType,
                'hourly_rate_type' => $hourlyRateType,
                'hourly_rate_multiplier' => $hourlyRateMultiplier,
                'base_rate_multiplier' => 1.0,
                'order' => $breakdownSequence++,
            ];

            $current = $nextBoundary->copy();
        }

        return $breakdown;
    }

    protected function getNextTimeBoundary(Carbon $current, Carbon $endTime): Carbon
    {
        $boundaries = [];

        // Add midnight boundary
        if ($current->format('H:i') !== '00:00') {
            $midnight = $current->copy()->addDay()->startOfDay();

            if ($midnight->lessThan($endTime)) {

                $boundaries[] = $midnight;
            }
        }

        if(!$this->attendanceScheduleIsFlexible){

            // Add night differential end boundary (start of regular hours)
            $nightDifferentialEnd = $current->copy()->startOfDay()->setTimeFromTimeString($this->nightDifferentialEnd());

            if ($nightDifferentialEnd->lessThanOrEqualTo($current)) {

                $nightDifferentialEnd->addDay();
            }

            if ($nightDifferentialEnd->lessThan($endTime)) {

                $boundaries[] = $nightDifferentialEnd;
            }

            // Add night start boundary (start of night hours)
            $nightDifferentialStart = $current->copy()->startOfDay()->setTimeFromTimeString($this->nightDifferentialStart());

            if ($nightDifferentialStart->lessThanOrEqualTo($current)) {

                $nightDifferentialStart->addDay();
            }

            if ($nightDifferentialStart->lessThan($endTime)) {

                $boundaries[] = $nightDifferentialStart;
            }
        }

        // Get the earliest boundary or end time
        if (!empty($boundaries)) {
            return collect($boundaries)->min();
        }

        return $endTime;
    }

    protected function getWorkHourType(Carbon $time): WorkHourType
    {
        if($this->attendanceScheduleIsFlexible){
            return WorkHourType::REGULAR;
        }

        $time = $time->format('H:i');

        if (TimeHelper::timeToMinutes($time) >= TimeHelper::timeToMinutes($this->nightDifferentialStart())
            || TimeHelper::timeToMinutes($time) < TimeHelper::timeToMinutes($this->nightDifferentialEnd())) {
            return WorkHourType::NIGHT;
        }

        return WorkHourType::REGULAR;
    }

    protected function getHourlyRate(Carbon $date, ?WorkHourType $workHourType, ShiftBreakDownSplitType $splitType): HourlyRateType
    {
        $dateString = $date->format('Y-m-d');
        $holidays = collect($this->holidays);
        $holidayDate = $holidays->where('date', $dateString)->first();

        $holidayType = !empty($holidayDate) ? $holidayDate['type'] : null;

        $restDay = in_array($date->dayOfWeek, $this->restDays);

        $workRates = [
            WorkHourType::NIGHT->value => [
                'rest' => [
                    HolidayType::SPECIAL->value => HourlyRateType::NIGHT_REST_SPECIAL_HOLIDAY,
                    HolidayType::LEGAL->value => HourlyRateType::NIGHT_REST_LEGAL_HOLIDAY,
                    HolidayType::DOUBLE->value => HourlyRateType::NIGHT_REST_DOUBLE_HOLIDAY,
                    'default' => HourlyRateType::NIGHT_REST,
                ],
                'work' => [
                    HolidayType::SPECIAL->value => HourlyRateType::NIGHT_SPECIAL_HOLIDAY,
                    HolidayType::LEGAL->value => HourlyRateType::NIGHT_LEGAL_HOLIDAY,
                    HolidayType::DOUBLE->value => HourlyRateType::NIGHT_DOUBLE_HOLIDAY,
                    'default' => HourlyRateType::NIGHT_REGULAR,
                ],
            ],
            WorkHourType::REGULAR->value => [
                'rest' => [
                    HolidayType::SPECIAL->value => HourlyRateType::REST_SPECIAL_HOLIDAY,
                    HolidayType::LEGAL->value => HourlyRateType::REST_LEGAL_HOLIDAY,
                    HolidayType::DOUBLE->value => HourlyRateType::REST_DOUBLE_HOLIDAY,
                    'default' => HourlyRateType::REST,
                ],
                'work' => [
                    HolidayType::SPECIAL->value => HourlyRateType::SPECIAL_HOLIDAY,
                    HolidayType::LEGAL->value => HourlyRateType::LEGAL_HOLIDAY,
                    HolidayType::DOUBLE->value => HourlyRateType::DOUBLE_HOLIDAY,
                    'default' => HourlyRateType::REGULAR,
                ],
            ],
        ];

        $overtimeRates = [
            WorkHourType::NIGHT->value => [
                'rest' => [
                    HolidayType::SPECIAL->value => HourlyRateType::OVERTIME_NIGHT_REST_SPECIAL_HOLIDAY,
                    HolidayType::LEGAL->value => HourlyRateType::OVERTIME_NIGHT_REST_LEGAL_HOLIDAY,
                    HolidayType::DOUBLE->value => HourlyRateType::OVERTIME_NIGHT_REST_DOUBLE_HOLIDAY,
                    'default' => HourlyRateType::OVERTIME_NIGHT_REST,
                ],
                'work' => [
                    HolidayType::SPECIAL->value => HourlyRateType::OVERTIME_NIGHT_SPECIAL_HOLIDAY,
                    HolidayType::LEGAL->value => HourlyRateType::OVERTIME_NIGHT_LEGAL_HOLIDAY,
                    HolidayType::DOUBLE->value => HourlyRateType::OVERTIME_NIGHT_DOUBLE_HOLIDAY,
                    'default' => HourlyRateType::OVERTIME_NIGHT_REGULAR,
                ],
            ],
            WorkHourType::REGULAR->value => [
                'rest' => [
                    HolidayType::SPECIAL->value => HourlyRateType::OVERTIME_REST_SPECIAL_HOLIDAY,
                    HolidayType::LEGAL->value => HourlyRateType::OVERTIME_REST_LEGAL_HOLIDAY,
                    HolidayType::DOUBLE->value => HourlyRateType::OVERTIME_REST_DOUBLE_HOLIDAY,
                    'default' => HourlyRateType::OVERTIME_REST,
                ],
                'work' => [
                    HolidayType::SPECIAL->value => HourlyRateType::OVERTIME_SPECIAL_HOLIDAY,
                    HolidayType::LEGAL->value => HourlyRateType::OVERTIME_LEGAL_HOLIDAY,
                    HolidayType::DOUBLE->value => HourlyRateType::OVERTIME_DOUBLE_HOLIDAY,
                    'default' => HourlyRateType::OVERTIME_REGULAR,
                ],
            ],
        ];

        $rateMapping = [
            ShiftBreakDownSplitType::WORK->value => $workRates,
            ShiftBreakDownSplitType::LUNCH->value => $workRates,
            ShiftBreakDownSplitType::OVERTIME->value => $overtimeRates,
        ];

        $dayType = $restDay ? 'rest' : 'work';
        $workHourKey = $workHourType->value;
        $holidayKey = $holidayType?->value ?? 'default';

        return $rateMapping[$splitType->value][$workHourKey][$dayType][$holidayKey]
            ?? $rateMapping[$splitType->value][$workHourKey][$dayType]['default'];
    }

    protected function getHourlyRateMultiplier(WorkHourType $workHourType, HourlyRateType $hourlyRateType, ShiftBreakDownSplitType $splitType)
    {
        $multiplier = 1;

        if($workHourType == WorkHourType::REGULAR){

            if(
                in_array($splitType, [ShiftBreakDownSplitType::WORK, ShiftBreakDownSplitType::LUNCH])
                && !empty($this->basicSalaryRegularRates)
            ){
                $multiplier = $this->basicSalaryRegularRates->where('hourly_rate_type', $hourlyRateType)->first()->value;
            }

            if(
                $splitType == ShiftBreakDownSplitType::OVERTIME
                && !empty($this->overtimeRegularRates)
            ){
                $multiplier = $this->overtimeRegularRates->where('hourly_rate_type', $hourlyRateType)->first()->value;
            }

        } else if($workHourType == WorkHourType::NIGHT){

            if(
                in_array($splitType, [ShiftBreakDownSplitType::WORK, ShiftBreakDownSplitType::LUNCH])
                && !empty($this->basicSalaryNightDifferentialRates)
            ){
                $multiplier = $this->basicSalaryNightDifferentialRates->where('hourly_rate_type', $hourlyRateType)->first()->value;
            }

            if(
                $splitType == ShiftBreakDownSplitType::OVERTIME
                && !empty($this->overtimeNightDifferentialRates)
            ){
                $multiplier = $this->overtimeNightDifferentialRates->where('hourly_rate_type', $hourlyRateType)->first()->value;
            }
        }

        return $multiplier;
    }

    protected function formatEndTime(Carbon $dateTime): string
    {
        // If it's exactly midnight (00:00), show as 24:00 of previous day
        if ($dateTime->format('H:i') === '00:00') {
            return '24:00';
        }

        return $dateTime->format('H:i');
    }

    protected function formatDuration(Carbon $start, Carbon $end): string
    {
        // Get total minutes difference
        $minutes = $start->diffInMinutes($end);

        // Convert minutes to HH:MM format
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }

    protected function nightDifferentialStart(): string
    {
        return $this->nightStart;
    }

    protected function nightDifferentialEnd(): string
    {
        return $this->nightEnd;
    }

    protected function shiftWorkStartGraceTime(): int
    {
        return $this->shiftWorkStartGraceTime;
    }

    protected function shiftRequireLunchOutAndIn(): bool
    {
        return $this->shiftRequireLunchOutAndIn &&
            $this->attendanceScheduleHasLunchBreak &&
            !$this->attendanceScheduleIsFlexible;
    }

    protected function shiftLunchStartGraceTime(): int
    {
        return $this->shiftLunchStartGraceTime;
    }

    protected function breakdownAttendance(array $breakdown, array $attendance): object
    {
        $debug = false;

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

        $breakdown = array_map(function ($split) {
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
            $this->shiftRequireLunchOutAndIn() &&
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
            $firstInCopy = $firstIn->copy()->subMinutes($this->shiftWorkStartGraceTime());

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
                            !$this->shiftRequireLunchOutAndIn() ||
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
                        !$this->shiftRequireLunchOutAndIn()
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
        if($this->shiftRequireLunchOutAndIn() && !empty($lunchOut) && !empty($lunchIn)){

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
                        $lunchOutCopy = $lunchOut->copy()->addMinutes($this->shiftLunchStartGraceTime());

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
                            $split['actual_end'] = $split['date'] . ' ' . $split['split_end'];
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
                        ($this->shiftRequireLunchOutAndIn() && $lastOut->gt($lunchIn))
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

                            $split['actual_end'] = $split['date'] . ' ' . $split['split_end'];
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
        if($this->shiftRequireLunchOutAndIn() && !empty($lunchOut) && !empty($lunchIn)){

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
                            $split['actual_end'] = $split['date'] . ' ' . $split['split_end'];
                        }
                    }

                    if($split['split_type'] == ShiftBreakDownSplitType::LUNCH){

                        if($split['actual_start'] == null){
                            $split['actual_start'] = $split['date'] . ' ' . $split['split_start'];
                        }

                        if($split['last_out']){

                            $split['actual_end'] = $split['date'] . ' ' . $split['split_end'];

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
                            $split['actual_end'] = $split['date'] . ' ' . $split['split_end'];
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

        if($this->shiftRequireLunchOutAndIn() && !empty($lunchOut) && !empty($lunchIn)){

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

        return (object)[
            'breakdown' => collect($breakdown)->sortBy('order')->values()->toArray(),
            'first_in' => $firstIn,
            'lunch_out' => $lunchOut,
            'lunch_in' => $lunchIn,
            'last_out' => $lastOut,
            'all_time_out_of_shift' => $allTimeOutOfShift
        ];
    }

    protected function justifySplitActualStartAndEnd(&$split): void
    {
        if($split['actual_start'] == null){
            $split['actual_start'] = $split['date'] . ' ' . $split['split_start'];
        }

        if($split['actual_end'] == null){
            $split['actual_end'] = $split['date'] . ' ' . $split['split_end'];
        }
    }

    /**
     * @throws NotFoundException
     */
    protected function breakdownIrregularities(object $attendance): array
    {
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
        }, $attendance->breakdown);

        $allTimeOutOfShift = $attendance->all_time_out_of_shift;
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
        if($this->shiftRequireLunchOutAndIn() && !is_numeric($firstInOrderSequence)){
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
            throw new NotFoundException("First in order sequence not found: breakdown @ breakdown irregularities [" . __LINE__ . "]");
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
                    !$this->attendanceScheduleIsFlexible
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
                        !$this->attendanceScheduleIsFlexible
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

                /**
                 * If first in, lunch out, lunch in and last out are in the same split
                 * and shift requires lunch out and in
                 * Deduct actual duration of total duration of lunch out and lunch in
                 * */
                if($split['first_in'] && $split['lunch_out'] && $split['lunch_in']&& $split['last_out'] && $this->shiftRequireLunchOutAndIn()){
                    $split['actual_irregularity_duration_start'] = $attendance->lunch_out->format('Y-m-d H:i');
                    $split['actual_irregularity_duration_end'] = $attendance->lunch_in->format('Y-m-d H:i');

                    $irregularityDuration = intval($attendance->lunch_out->diffInMinutes($attendance->lunch_in, true));
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
                    !$this->attendanceScheduleIsFlexible
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
            $this->attendanceScheduleIsFlexible
        ){

            $totalActualPresent = collect($attendanceBreakdown)
                ->where('split_type', ShiftBreakDownSplitType::WORK)
                ->sum('actual_present');

            $flexibleUndertime =  $totalActualPresent >= $this->attendanceScheduleTotalWorkHoursWithBreaks
                ? 0
                : $this->attendanceScheduleTotalWorkHoursWithBreaks - $totalActualPresent;

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
}
