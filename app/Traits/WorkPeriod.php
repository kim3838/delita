<?php

namespace App\Traits;

use App\Blueprint\Repositories\CompanyFormulaRepository;
use App\Enums\Compensation;
use App\Enums\Formulable;
use App\Enums\HourlyRateType;
use App\Exceptions\NotFoundException;
use App\Facades\Fractal;
use App\Helpers\TimeHelper;
use App\Models\Shift;
use App\Transformers\ShiftSchedule\PatchableTransformer;
use Carbon\Carbon;
use Illuminate\Support\Collection;

trait WorkPeriod
{
    protected ?Collection $companyBasicSalaryFormulaSettings = null;
    protected ?Collection $companyOvertimeFormulaSettings = null;
    protected ?Collection $basicSalaryRegularRates = null;
    protected ?Collection $basicSalaryNightDifferentialRates = null;
    protected ?Collection $overtimeRegularRates = null;
    protected ?Collection $overtimeNightDifferentialRates = null;

    function resolveCompanyFormulaSettings(): void
    {
        $this->companyBasicSalaryFormulaSettings = $this->companyBasicSalaryFormulaSettings();
        $this->companyOvertimeFormulaSettings = $this->companyOvertimeFormulaSettings();
        $this->basicSalaryRegularRates = $this->getBasicSalaryRegularRates();
        $this->basicSalaryNightDifferentialRates = $this->getBasicSalaryNightDifferentialRates();
        $this->overtimeRegularRates = $this->getOvertimeRegularRates();
        $this->overtimeNightDifferentialRates = $this->getOvertimeNightDifferentialRates();
    }

    function resolveCompanyNightHoursFromBasicSalaryFormulaSettings(): void
    {
        if(empty($this->companyBasicSalaryFormulaSettings)){
            return;
        }

        $companyNightDifferentialHours = collect(
            $this->companyBasicSalaryFormulaSettings
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

    function companyBasicSalaryFormulaSettings(): ?Collection
    {
        return $this->getCompanyFormulaSettings(Formulable::EARNINGS->value, Compensation::BASIC_SALARY->value);
    }

    function companyOvertimeFormulaSettings(): ?Collection
    {
        return $this->getCompanyFormulaSettings(Formulable::EARNINGS->value, Compensation::OVERTIME->value);
    }

    function getBasicSalaryRegularRates(): ?Collection
    {
        if(empty($this->companyBasicSalaryFormulaSettings)){
            return null;
        }

        $companyBasicSalaryRegularRates = collect(
            $this->companyBasicSalaryFormulaSettings
                ->where('key', 'regular_rates')
                ->first()
                ->value
        )->sortBy('order');

        return $companyBasicSalaryRegularRates->map(function ($rate){

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

    function getBasicSalaryNightDifferentialRates(): ?Collection
    {
        if(empty($this->companyBasicSalaryFormulaSettings)){
            return null;
        }

        $companyBasicSalaryNightDifferentialRates = collect(
            $this->companyBasicSalaryFormulaSettings
                ->where('key', 'night_differential_rates')
                ->first()
                ->value
        )->sortBy('order');

        return $companyBasicSalaryNightDifferentialRates->map(function ($rate){

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

    function getOvertimeRegularRates(): ?Collection
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

    function getOvertimeNightDifferentialRates(): ?Collection
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
     * @throws NotFoundException
     */
    function setShift($shiftId): void
    {
        $this->resetShift();

        //Attendance shift
        $this->shift = Shift::query()->find($shiftId);

        if(empty($this->shift)){
            throw new NotFoundException('Shift not found');
        }

        $shift = clone $this->shift;

        $this->schedules = Fractal::collection($shift->schedules, PatchableTransformer::class)['data'];

        $this->restDays = collect($this->schedules)
            ->filter(fn($schedule)=>$schedule['is_rest_day'])
            ->map(function ($schedule){return $schedule['week_day'];})
            ->values()
            ->all();

        $this->shiftWorkStartGraceTime = $this->shift->work_start_grace_time;
        $this->shiftRequireLunchOutAndIn = $this->shift->require_lunch_time_in_and_out;
        $this->shiftLunchStartGraceTime = $this->shift->lunch_start_grace_time;
        $this->shiftOvertimeLimit = $this->shift->max_overtime;
    }

    /**
     * @throws NotFoundException
     */
    function setAttendanceSchedule(Carbon $attendanceDate): void
    {
        $attendanceDayOfWeek = $attendanceDate->dayOfWeek;

        $this->attendanceSchedule = collect($this->schedules)
            ->filter(fn($schedule) => $schedule['week_day'] == $attendanceDayOfWeek)
            ->first();

        if(empty($this->attendanceSchedule)){
            throw new NotFoundException("Attendance schedule not found: scaffolder @ set attendance schedule [" . __LINE__ . "]");
        }

        $this->attendanceScheduleHasLunchBreak = boolval($this->attendanceSchedule['has_lunch_break']);
        $this->attendanceScheduleIsDayOff = $this->attendanceSchedule['is_day_off'];
        $this->attendanceScheduleIsFlexible = $this->attendanceSchedule['is_flexible'];
        $this->attendanceScheduleTotalWorkHoursWithBreaks = TimeHelper::timeToMinutes($this->attendanceSchedule['total_work_hours_with_breaks']);
    }

    function resetShift(): void
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
}
