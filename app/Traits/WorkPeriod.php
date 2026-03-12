<?php

namespace App\Traits;

use App\Blueprint\Repositories\CompanyFormulaRepository;
use App\Blueprint\Repositories\ShiftRepository;
use App\Blueprint\Repositories\ShiftScheduleRepository;
use App\Enums\Compensation;
use App\Enums\Formulable;
use App\Enums\HolidayType;
use App\Enums\HourlyRateType;
use App\Enums\ShiftBreakDownSplitType;
use App\Enums\ShiftHolidayPolicy;
use App\Enums\WorkHourType;
use App\Exceptions\UnexpectedException;
use App\Facades\Fractal;
use App\Helpers\TimeHelper;
use App\Models\Holiday;
use App\Models\Shift;
use App\Transformers\Shift\PatchableTransformer as ShiftPatchableTransformer;
use App\Transformers\ShiftSchedule\PatchableTransformer;
use App\Transformers\ShiftSchedule\PatchableTransformer as ShiftSchedulePatchableTransformer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

trait WorkPeriod
{
    //Set on construct
    protected string $nightStart = '22:00';
    //Set on construct
    protected string $nightEnd = '06:00';
    //Set on generate: attendance shift
    protected ?Shift $shift = null;
    //Set on generate: shift work_start_grace_time
    protected int $shiftWorkStartGraceTime = 0;
    //Set on generate: shift holiday_policy
    protected ?ShiftHolidayPolicy $shiftHolidayPolicy = null;
    //Set on generate: shift except_holidays
    protected array $shiftExceptHolidays = [];
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
    protected ?Collection $companyBasicPayFormulaSettings = null;
    protected ?Collection $companyOvertimeFormulaSettings = null;
    protected ?Collection $basicPayRegularRates = null;
    protected ?Collection $basicPayNightDifferentialRates = null;
    protected ?Collection $overtimeRegularRates = null;
    protected ?Collection $overtimeNightDifferentialRates = null;

    use HasTime;

    function resolveCompanyFormulaSettings(): void
    {
        $this->companyBasicPayFormulaSettings = $this->companyBasicPayFormulaSettings();
        $this->companyOvertimeFormulaSettings = $this->companyOvertimeFormulaSettings();
        $this->basicPayRegularRates = $this->getBasicPayRegularRates();
        $this->basicPayNightDifferentialRates = $this->getBasicPayNightDifferentialRates();
        $this->overtimeRegularRates = $this->getOvertimeRegularRates();
        $this->overtimeNightDifferentialRates = $this->getOvertimeNightDifferentialRates();
    }

    function resolveCompanyNightHoursFromBasicPayFormulaSettings(): void
    {
        if(empty($this->companyBasicPayFormulaSettings)){
            return;
        }

        $companyNightDifferentialHours = collect(
            $this->companyBasicPayFormulaSettings
                ->where('key', 'night_differential_hours')
                ->first()
                ->value
        );

        $nightStart = $companyNightDifferentialHours->where('key', 'start_time')->first()?->value;
        $nightEnd = $companyNightDifferentialHours->where('key', 'end_time')->first()?->value;

        $this->nightStart = !empty($nightStart) ? Carbon::parse($nightStart)->format('H:i') : null;
        $this->nightEnd = !empty($nightEnd) ? Carbon::parse($nightEnd)->format('H:i') : null;
    }

    private function getCompanyFormulaSettings($formulableType, $componentType): ?Collection
    {
        $company = clone $this->company;
        $companyFormula = $company
            ->formulas
            ->where('formulable_type', $formulableType)
            ->where('component_type', $componentType)
            ->first();

        if (empty($companyFormula)) {
            return null;
        }

        $companyFormulaHydrated = app(CompanyFormulaRepository::class)
            ->hydrateItem($companyFormula->pivot->toArray());

        return collect($companyFormulaHydrated->settings->parsed)
            ->sortBy('order');
    }

    protected function companyBasicPayFormulaSettings(): ?Collection
    {
        return $this->getCompanyFormulaSettings(Formulable::EARNINGS->value, Compensation::BASIC_PAY->value);
    }

    protected function companyOvertimeFormulaSettings(): ?Collection
    {
        return $this->getCompanyFormulaSettings(Formulable::EARNINGS->value, Compensation::OVERTIME->value);
    }

    protected function getBasicPayRegularRates(): ?Collection
    {
        if(empty($this->companyBasicPayFormulaSettings)){
            return null;
        }

        $companyBasicPayRegularRates = collect(
            $this->companyBasicPayFormulaSettings
                ->where('key', 'regular_rates')
                ->first()
                ->value
        )->sortBy('order');

        return $companyBasicPayRegularRates->map(function ($rate){

            $rate_type = match($rate->key){
                'regular' => HourlyRateType::REGULAR,
                'rest_day' => HourlyRateType::REST,
                'special_holiday' => HourlyRateType::SPECIAL_HOLIDAY,
                'special_holiday_and_rest_day' => HourlyRateType::REST_SPECIAL_HOLIDAY,
                'legal_holiday' => HourlyRateType::LEGAL_HOLIDAY,
                'legal_holiday_and_rest_day' => HourlyRateType::REST_LEGAL_HOLIDAY,
                'double_holiday' => HourlyRateType::DOUBLE_HOLIDAY,
                'double_holiday_and_rest_day' => HourlyRateType::REST_DOUBLE_HOLIDAY,
                default => null,
            };

            return (object)[
                'key' => $rate->key,
                'hourly_rate_type' => $rate_type,
                'value' => $rate->value,
            ];
        });
    }

    protected function getBasicPayNightDifferentialRates(): ?Collection
    {
        if(empty($this->companyBasicPayFormulaSettings)){
            return null;
        }

        $companyBasicPayNightDifferentialRates = collect(
            $this->companyBasicPayFormulaSettings
                ->where('key', 'night_differential_rates')
                ->first()
                ->value
        )->sortBy('order');

        return $companyBasicPayNightDifferentialRates->map(function ($rate){

            $rate_type = match($rate->key){
                'regular' => HourlyRateType::NIGHT_REGULAR,
                'rest_day' => HourlyRateType::NIGHT_REST,
                'special_holiday' => HourlyRateType::NIGHT_SPECIAL_HOLIDAY,
                'special_holiday_and_rest_day' => HourlyRateType::NIGHT_REST_SPECIAL_HOLIDAY,
                'legal_holiday' => HourlyRateType::NIGHT_LEGAL_HOLIDAY,
                'legal_holiday_and_rest_day' => HourlyRateType::NIGHT_REST_LEGAL_HOLIDAY,
                'double_holiday' => HourlyRateType::NIGHT_DOUBLE_HOLIDAY,
                'double_holiday_and_rest_day' => HourlyRateType::NIGHT_REST_DOUBLE_HOLIDAY,
                default => null,
            };

            return (object)[
                'key' => $rate->key,
                'hourly_rate_type' => $rate_type,
                'value' => $rate->value,
            ];
        });
    }

    protected function getOvertimeRegularRates(): ?Collection
    {
        if(empty($this->companyOvertimeFormulaSettings)){
            return null;
        }

        $companyOvertimeRegularRates = collect(
            $this->companyOvertimeFormulaSettings
                ->where('key', 'regular_rates')
                ->first()
                ->value
        )->sortBy('order');

        return $companyOvertimeRegularRates->map(function ($rate){

            $rate_type = match($rate->key){
                'regular' => HourlyRateType::OVERTIME_REGULAR,
                'rest_day' => HourlyRateType::OVERTIME_REST,
                'special_holiday' => HourlyRateType::OVERTIME_SPECIAL_HOLIDAY,
                'special_holiday_and_rest_day' => HourlyRateType::OVERTIME_REST_SPECIAL_HOLIDAY,
                'legal_holiday' => HourlyRateType::OVERTIME_LEGAL_HOLIDAY,
                'legal_holiday_and_rest_day' => HourlyRateType::OVERTIME_REST_LEGAL_HOLIDAY,
                'double_holiday' => HourlyRateType::OVERTIME_DOUBLE_HOLIDAY,
                'double_holiday_and_rest_day' => HourlyRateType::OVERTIME_REST_DOUBLE_HOLIDAY,
                default => null,
            };

            return (object)[
                'key' => $rate->key,
                'hourly_rate_type' => $rate_type,
                'value' => $rate->value,
            ];
        });
    }

    protected function getOvertimeNightDifferentialRates(): ?Collection
    {
        if(empty($this->companyOvertimeFormulaSettings)){
            return null;
        }

        $companyOvertimeNightDifferentialRates = collect(
            $this->companyOvertimeFormulaSettings
                ->where('key', 'night_differential_rates')
                ->first()
                ->value
        )->sortBy('order');

        return $companyOvertimeNightDifferentialRates->map(function ($rate){

            $rate_type = match($rate->key){
                'regular' => HourlyRateType::OVERTIME_NIGHT_REGULAR,
                'rest_day' => HourlyRateType::OVERTIME_NIGHT_REST,
                'special_holiday' => HourlyRateType::OVERTIME_NIGHT_SPECIAL_HOLIDAY,
                'special_holiday_and_rest_day' => HourlyRateType::OVERTIME_NIGHT_REST_SPECIAL_HOLIDAY,
                'legal_holiday' => HourlyRateType::OVERTIME_NIGHT_LEGAL_HOLIDAY,
                'legal_holiday_and_rest_day' => HourlyRateType::OVERTIME_NIGHT_REST_LEGAL_HOLIDAY,
                'double_holiday' => HourlyRateType::OVERTIME_NIGHT_DOUBLE_HOLIDAY,
                'double_holiday_and_rest_day' => HourlyRateType::OVERTIME_NIGHT_REST_DOUBLE_HOLIDAY,
                default => null,
            };

            return (object)[
                'key' => $rate->key,
                'hourly_rate_type' => $rate_type,
                'value' => $rate->value,
            ];
        });
    }

    /**
     * @throws UnexpectedException
     */
    function setShift(int|Shift $shift): void
    {
        $this->resetShift();

        //Attendance shift
        $this->shift = $shift instanceof Shift
            ? $shift
            : Shift::query()->find($shift);

        if(empty($this->shift)){
            throw new UnexpectedException('Shift not found');
        }

        $shift = clone $this->shift;

        $this->schedules = Fractal::collection($shift->schedules, PatchableTransformer::class)['data'];

        $this->restDays = collect($this->schedules)
            ->filter(fn($schedule)=>$schedule['is_rest_day'])
            ->map(function ($schedule){return $schedule['week_day'];})
            ->values()
            ->all();

        $this->shiftHolidayPolicy = $this->shift->holiday_policy;
        $this->shiftExceptHolidays = is_array($this->shift->except_holidays) ? $this->shift->except_holidays : [];
        $this->shiftWorkStartGraceTime = $this->shift->work_start_grace_time;
        $this->shiftRequireLunchOutAndIn = $this->shift->require_lunch_time_in_and_out;
        $this->shiftLunchStartGraceTime = $this->shift->lunch_start_grace_time;
        $this->shiftOvertimeLimit = $this->shift->max_overtime;
    }

    /**
     * @throws UnexpectedException
     */
    protected function setAttendanceSchedule(Carbon $attendanceDate): void
    {
        $attendanceDayOfWeek = $attendanceDate->dayOfWeek;

        $this->attendanceSchedule = collect($this->schedules)
            ->filter(fn($schedule) => $schedule['week_day'] == $attendanceDayOfWeek)
            ->first();

        if(empty($this->attendanceSchedule)){
            throw new UnexpectedException("Attendance schedule not found: T.WorkPeriod@setAttendanceSchedule [" . __LINE__ . "]");
        }

        $this->attendanceScheduleHasLunchBreak = boolval($this->attendanceSchedule['has_lunch_break']);
        $this->attendanceScheduleIsDayOff = $this->attendanceSchedule['is_day_off'];
        $this->attendanceScheduleIsFlexible = $this->attendanceSchedule['is_flexible'];
        $this->attendanceScheduleTotalWorkHoursWithBreaks = TimeHelper::timeToMinutes($this->attendanceSchedule['total_work_hours_with_breaks']);
    }

    protected function resetShift(): void
    {
        $this->shift = null;
        $this->shiftWorkStartGraceTime = 0;
        $this->shiftRequireLunchOutAndIn = false;
        $this->shiftLunchStartGraceTime = 0;
        $this->shiftOvertimeLimit = 0;
        $this->restDays = [];
        $this->schedules = [];
        $this->attendanceSchedule = null;
        $this->attendanceScheduleHasLunchBreak = false;
        $this->attendanceScheduleIsDayOff = false;
        $this->attendanceScheduleIsFlexible = false;
        $this->attendanceScheduleTotalWorkHoursWithBreaks = 0;
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

    /**
     * @throws UnexpectedException
     */
    protected function breakdownWorkPeriods(array $periods, $startingDateIsRestDay, ?HolidayType $startingDateHolidayType, $splitTypes = []): array
    {
        $breakdownSequence = 1;
        $schedule = [];
        $overtime = [];

        foreach ($periods as $period) {

            // Split work periods into hourly segments and categorize
            $categorizedSplit = $this->categorizeWorkPeriod($period['split_start'], $period['split_end'], $period['split_type'], $breakdownSequence, $startingDateIsRestDay, $startingDateHolidayType);

            $splitTypes = empty($splitTypes) ? [ShiftBreakDownSplitType::WORK, ShiftBreakDownSplitType::LUNCH] : $splitTypes;

            if(in_array($period['split_type'], $splitTypes) && !empty($categorizedSplit)){

                $schedule = array_merge($schedule, $categorizedSplit);
            }

            if($period['split_type'] == ShiftBreakDownSplitType::OVERTIME && !empty($categorizedSplit)){

                $overtime = array_merge($overtime, $categorizedSplit);
            }
        }

        return [
            $schedule,
            $overtime
        ];
    }

    /**
     * @throws UnexpectedException
     */
    protected function categorizeWorkPeriod(Carbon $startTime, Carbon $endTime, ShiftBreakDownSplitType $splitType, &$breakdownSequence, $startingDateIsRestDay, ?HolidayType $startingDateHolidayType): array
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
            $hourlyRateType = $this->getHourlyRate($current, $workHourType, $splitType, $startingDateIsRestDay, $startingDateHolidayType);

            /**
             * Include regular rate if rate is night multiplier
             * Include non rest rate multiplier if rate is rest multiplier
             * */
            list($regularMultiplier, $nonRestRateMultiplier, $multiplier) = $this->getHourlyRateMultiplier($workHourType, $hourlyRateType, $splitType);

            $baseMultiplier = $this->getSplitTypeBaseMultiplier($splitType);

            $breakdown[] = [
                'date' => $current->format('Y-m-d'),
                'split_type' => $splitType,
                'split_start' => $current->format('H:i'),
                'split_end' => $this->formatEndTime($nextBoundary),
                'split_duration' => $duration,
                '#split_duration_readable' => $this->formatDuration($current, $nextBoundary),
                'work_hour_type' => $workHourType,
                'hourly_rate_type' => $hourlyRateType,
                'regular_rate_multiplier' => $regularMultiplier,
                'non_rest_rate_multiplier' => $nonRestRateMultiplier,
                'hourly_rate_multiplier' => $multiplier,
                'base_rate_multiplier' => $baseMultiplier,
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

    protected function getHourlyRate(Carbon $date, ?WorkHourType $workHourType, ShiftBreakDownSplitType $splitType, $overrideIsRestDay, ?HolidayType $overrideHolidayType): HourlyRateType
    {
        $everySplitHaveTheirOwnHolidayType = false;//Every split have their own holiday type
        $everySplitHaveTheirOwnDayType = false;//Every split have their own rest or regular day

        if($everySplitHaveTheirOwnHolidayType){
            $dateString = $date->format('Y-m-d');
            $holidayType = $this->getDateHolidayType($dateString);
        } else {
            $holidayType = $overrideHolidayType;
        }

        if($everySplitHaveTheirOwnDayType){
            $restDay = in_array($date->dayOfWeek, $this->restDays);
        } else {
            $restDay = $overrideIsRestDay;
        }

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

    /**
     * @throws UnexpectedException
     */
    protected function getHourlyRateMultiplier(WorkHourType $workHourType, HourlyRateType $hourlyRateType, ShiftBreakDownSplitType $splitType): array
    {
        /**
         * If multiplier is night, include a regular multiplier by renaming the HourlyRateType by its regular version
         **/
        $regularMultiplier = null;
        /**
         * If multiplier is from a rest day, get its non-rest version
         **/
        $hourlyRateIsRest = str_contains($hourlyRateType->name, 'REST');
        $nonRestMultiplier = null;
        $nonRestHourlyRate = null;

        if($hourlyRateIsRest){
            $nonRestHourlyRate = HourlyRateType::tryFrom($hourlyRateType->value - 1);

            if(empty($nonRestHourlyRate)){
                throw new UnexpectedException('Non-rest rate not found');
            }
        }

        $multiplier = 1;

        if($workHourType == WorkHourType::REGULAR){

            if(
                in_array($splitType, [ShiftBreakDownSplitType::WORK, ShiftBreakDownSplitType::LUNCH])
                && !empty($this->basicPayRegularRates)
            ){
                if ($hourlyRateIsRest) {
                    $nonRestMultiplier = $this->basicPayRegularRates->where('hourly_rate_type', $nonRestHourlyRate)->first()->value;
                }

                $multiplier = $this->basicPayRegularRates->where('hourly_rate_type', $hourlyRateType)->first()->value;
            }

            if(
                $splitType == ShiftBreakDownSplitType::OVERTIME
                && !empty($this->overtimeRegularRates)
            ){
                if ($hourlyRateIsRest) {
                    $nonRestMultiplier = $this->overtimeRegularRates->where('hourly_rate_type', $nonRestHourlyRate)->first()->value;
                }

                $multiplier = $this->overtimeRegularRates->where('hourly_rate_type', $hourlyRateType)->first()->value;
            }

        } else if($workHourType == WorkHourType::NIGHT){

            if(
                in_array($splitType, [ShiftBreakDownSplitType::WORK, ShiftBreakDownSplitType::LUNCH])
                && !empty($this->basicPayNightDifferentialRates)
            ){
                $regularRateTypeVersion = str_replace('NIGHT_', '', $hourlyRateType->name);

                $regularMultiplier = $this->basicPayRegularRates->where('hourly_rate_type', HourlyRateType::{$regularRateTypeVersion})->first()?->value;

                if(empty($regularMultiplier)){
                    throw new UnexpectedException('Regular rate not found');
                }

                if ($hourlyRateIsRest) {
                    $nonRestMultiplier = $this->basicPayNightDifferentialRates->where('hourly_rate_type', $nonRestHourlyRate)->first()->value;
                }

                $multiplier = $this->basicPayNightDifferentialRates->where('hourly_rate_type', $hourlyRateType)->first()->value;
            }

            if(
                $splitType == ShiftBreakDownSplitType::OVERTIME
                && !empty($this->overtimeNightDifferentialRates)
            ){
                $regularRateTypeVersion = str_replace('_NIGHT_', '_', $hourlyRateType->name);

                $regularMultiplier = $this->overtimeRegularRates->where('hourly_rate_type', HourlyRateType::{$regularRateTypeVersion})->first()?->value;

                if(empty($regularMultiplier)){
                    throw new UnexpectedException('Regular rate not found');
                }

                if ($hourlyRateIsRest) {
                    $nonRestMultiplier = $this->overtimeNightDifferentialRates->where('hourly_rate_type', $nonRestHourlyRate)->first()->value;
                }

                $multiplier = $this->overtimeNightDifferentialRates->where('hourly_rate_type', $hourlyRateType)->first()->value;
            }
        }

        return [$regularMultiplier, $nonRestMultiplier, $multiplier];
    }

    protected function getSplitTypeBaseMultiplier(ShiftBreakDownSplitType $splitType): float
    {
        $baseMultiplier = match($splitType){
            ShiftBreakDownSplitType::WORK => $this->basicPayRegularRates?->where('hourly_rate_type', HourlyRateType::REGULAR)->first()?->value ?? 1.0,
            ShiftBreakDownSplitType::OVERTIME => $this->overtimeRegularRates?->where('hourly_rate_type', HourlyRateType::OVERTIME_REGULAR)->first()?->value ?? 1.0,
            default => 1.0,
        };

        return (float)$baseMultiplier;
    }

    protected function getDateHolidayType($date): ?HolidayType
    {
        $holiday = $this->getCompanyHolidayByDate($date, $this->company->id);

        return !empty($holiday) ? $holiday->type : null;
    }

    protected function getDateHolidayPayForfeiture($date): ?bool
    {
        $holiday = $this->getCompanyHolidayByDate($date, $this->company->id);

        return !empty($holiday) ? $holiday->holiday_pay_forfeiture : false;
    }

    protected function getCompanyHolidayByDate(string $date, $companyId = null): ?Holiday
    {
        if(empty($companyId)){
            return null;
        }

        // Convert date to Carbon instance for easier manipulation
        $searchDate = Carbon::parse($date);

        $exceptHolidays = $this->shiftExceptHolidays;

        $queryBuilder = Holiday::query()
            ->when(!empty($exceptHolidays), function ($builder) use ($exceptHolidays) {
                $builder->whereNotIn('id', $exceptHolidays);
            })
            ->where('active', true)
            ->where('company_id', $companyId)
            ->where('effective_date', '<=', $searchDate->format('Y-m-d'))
            ->where(function ($query) use ($searchDate) {
                // For recurring holidays: match month and day
                $query->where(function ($subQuery) use ($searchDate) {
                    $subQuery->where('recurring', true)
                        ->whereRaw('MONTH(date) = ?', [$searchDate->month])
                        ->whereRaw('DAY(date) = ?', [$searchDate->day]);
                })
                // For non-recurring holidays: exact date match
                ->orWhere(function ($subQuery) use ($searchDate) {
                    $subQuery->where('recurring', false)
                        ->where('date', $searchDate->format('Y-m-d'));
                });
            });

        return $queryBuilder->first();
    }

    /**
     * Validate attendance shift details if still match the current shift and schedule settings
     * */
    protected function validateAttendanceShiftDetails(
        $shift,
        $shiftSchedule,
        $attendanceShift,
        $attendanceShiftSchedule,
    ): array {

        $currentShift = Fractal::item($shift, ShiftPatchableTransformer::class);

        $currentShiftScheduleHydrated = App::make(ShiftScheduleRepository::class)->hydrateItem($shiftSchedule);
        $currentShiftSchedule = Fractal::item($currentShiftScheduleHydrated, ShiftSchedulePatchableTransformer::class);

        //Transform except holidays into raw value, not the array form
        $attendanceShift = [
            ...$attendanceShift,
            'except_holidays' => is_array($attendanceShift['except_holidays']) ? json_encode($attendanceShift['except_holidays']) : null,
        ];

        $attendanceShiftHydrated = App::make(ShiftRepository::class)->hydrateItem($attendanceShift);
        $attendanceShift = Fractal::item($attendanceShiftHydrated, ShiftPatchableTransformer::class);

        $attendanceShiftScheduleHydrated = App::make(ShiftScheduleRepository::class)->hydrateItem($attendanceShiftSchedule);
        $attendanceShiftSchedule = Fractal::item($attendanceShiftScheduleHydrated, ShiftSchedulePatchableTransformer::class);

        $currentShiftAndAttendanceShiftStillTheSame = collect($currentShift)->except(['id', 'ulid', 'company_id', 'code', 'name'])->toArray()
            == collect($attendanceShift)->except(['id', 'ulid', 'company_id', 'code', 'name'])->toArray();

        $currentShiftScheduleAndAttendanceShiftScheduleStillTheSame = collect($currentShiftSchedule)->except(['id', 'shift_id', 'week_day_name'])->toArray()
            == collect($attendanceShiftSchedule)->except(['id', 'shift_id', 'week_day_name'])->toArray();

        return [
            $currentShiftAndAttendanceShiftStillTheSame,
            $currentShiftScheduleAndAttendanceShiftScheduleStillTheSame
        ];
    }
}
