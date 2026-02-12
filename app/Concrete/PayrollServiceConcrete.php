<?php

namespace App\Concrete;

use App\Blueprint\PayrollServiceInterface;
use App\Blueprint\Repositories\AttendanceRepository;
use App\Blueprint\Repositories\EmployeePayrollComponentRepository;
use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\LeaveRepository;
use App\Blueprint\Repositories\LeaveTypeRepository;
use App\Blueprint\Repositories\PayFrequencyRepository;
use App\Blueprint\Repositories\PayrollPayloadRepository;
use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Enums\AttendanceStatus;
use App\Enums\Compensation as CompensationEnum;
use App\Enums\Formulable;
use App\Enums\HolidayType;
use App\Enums\PayFrequency as PayFrequencyEnum;
use App\Enums\PayType;
use App\Enums\SalaryStatementAttendanceDayType;
use App\Enums\SalaryStatementAttendanceStatus;
use App\Enums\SemiMonthlySequence;
use App\Enums\ShiftBreakDownSplitType;
use App\Enums\ShiftHolidayPolicy;
use App\Enums\WorkHourType;
use App\Exceptions\UnexpectedException;
use App\Facades\Fractal;
use App\Models\Company;
use App\Models\Compensation;
use App\Models\Employee;
use App\Models\Hydrations\Payroll\PayrollPayload;
use App\Models\PayFrequency as PayFrequencyModel;
use App\Models\Payroll;
use App\Models\SalaryStatementAttendance;
use App\Models\Shift;
use App\Traits\HasPayableDay;
use App\Traits\HasTime;
use App\Traits\WorkPeriod;
use App\Transformers\Attendance\PatchableTransformer as AttendancePatchableTransformer;
use App\Transformers\AttendanceDetail\PayableSplitTransformer as AttendanceDetailPayableSplitTransformer;
use App\Transformers\Leave\BasicTransformer as LeaveBasicTransformer;
use App\Transformers\PayrollPayload\BasicTransformer;
use App\Transformers\SalaryStatementAttendanceDetail\PayableSplitTransformer as SalaryStatementAttendanceDetailPayableSplitTransformer;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class PayrollServiceConcrete implements PayrollServiceInterface
{
    public ?Payroll $payroll;
    public string $timezone = 'UTC';
    public Carbon $date;
    public Collection $payFrequencies;
    public int $frequencyWorkingDayCount = 0;

    use HasTime, HasPayableDay, WorkPeriod;

    public function __construct(
        protected ?Company $company
    ){
        $this->timezone = $company?->timezone ?? 'UTC';
        $this->date = Carbon::now()->timezone($this->timezone);
        $this->payFrequencies = $company?->payFrequencies ?? collect();
    }

    public function resetDate(): static
    {
        $this->date = Carbon::now()->timezone($this->timezone);

        return $this;
    }

    public function setPayroll(Payroll $payroll): static
    {
        $this->payroll = $payroll;

        return $this;
    }

    public function setCustomDate(Carbon $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getLatestWithRecent($payFrequencyEnumValue = [], $recentCount = 1): array
    {
        $recentPayrolls = [];
        $latestPayrolls = [];

        sort($payFrequencyEnumValue);

        foreach($payFrequencyEnumValue as $payFrequency){

            $payFrequencyRecentPayrolls = [];

            $payFrequencyLatestPayroll = $this->getPayrollPayload($payFrequency);
            $chainStartDate = $payFrequencyLatestPayroll->start->copy();

            while(count($payFrequencyRecentPayrolls) < $recentCount){

                $this->setCustomDate($chainStartDate->subDay());

                $recent = $this->getPayrollPayload($payFrequency);

                $chainStartDate = $recent->start->copy();

                array_unshift($payFrequencyRecentPayrolls, $recent);
            }

            $latestPayrolls[] = $payFrequencyLatestPayroll;

            foreach($payFrequencyRecentPayrolls as $payFrequencyRecentPayroll){
                $recentPayrolls[] = $payFrequencyRecentPayroll;
            }

            $this->resetDate();
        }

        return [
            'recent' => collect($recentPayrolls),
            'latest' => collect($latestPayrolls),
        ];
    }

    public function getPayrollPayload($payFrequencyEnumValue = null): ?PayrollPayload
    {
        $debugEnabled = false;

        if(empty($this->company)){
            return null;
        }

        if($debugEnabled){
            $weekly = PayFrequencyEnum::WEEKLY;
            $semimonthly = PayFrequencyEnum::SEMI_MONTHLY;
            $monthly = PayFrequencyEnum::MONTHLY;

            $weeklyFrequency = $this->payFrequencies->where('type', $weekly->value)->first();
            $semimonthlyFrequency = $this->payFrequencies->where('type', $semimonthly->value)->first();
            $monthlyFrequency = $this->payFrequencies->where('type', $monthly->value)->first();

            $latestWeekly = $this->getPayrollPayloadByFrequency($weeklyFrequency);
            $latestMonthlyFrequency = $this->getPayrollPayloadByFrequency($monthlyFrequency);
            $latestSemimonthlyFrequency = $this->getPayrollPayloadByFrequency($semimonthlyFrequency);

            _debug([
                'date' => $this->date->toDateString(),
                'weekly' => Fractal::item($latestWeekly, BasicTransformer::class),
                'monthly' => Fractal::item($latestMonthlyFrequency, BasicTransformer::class),
                'semimonthly' => Fractal::item($latestSemimonthlyFrequency, BasicTransformer::class),
            ]);
        }

        $payrollPayload = $this->getPayrollPayloadByFrequency($this->payFrequencies->where('type', $payFrequencyEnumValue)->first());

        return $payrollPayload;
    }

    /**
     * @throws BindingResolutionException
     */
    private function getPayrollPayloadByFrequency(?PayFrequencyModel $frequency): PayrollPayload
    {
        $payrollYear = null;
        $payrollMonth = null;
        $frequencySequence = null;
        $startDate = null;
        $endDate = null;

        if($frequency->type == PayFrequencyEnum::WEEKLY){

            $startDate = $this->getPreviousWeekDay($this->date, $frequency->cut_off_value->value)->addDay();

            if($this->date->dayOfWeek == $frequency->cut_off_value->value){

                $endDate = $this->date;

            } else {
                $endDate = $startDate->copy()->addDays($frequency->days_span - 1);
            }

            $payrollYear = $endDate->year;
            $payrollMonth = $endDate->month;
        }

        if($frequency->type == PayFrequencyEnum::MONTHLY){

            if($frequency->timePeriodPreset->name == 'end_of_month_cut_off'){

                $startDate = $this->date->copy()->startOfMonth();
                $endDate = $this->date->copy()->endOfMonth();
                $payrollYear = $startDate->year;
                $payrollMonth = $startDate->month;
            } else {

                $startDay = (int)collect($frequency->period->cast)->where('key', 'start_date')->first()->value->day;
                $endDay = (int)collect($frequency->period->cast)->where('key', 'end_date')->first()->value->day;

                $startDate = $this->getPreviousNthIncludingCurrent($this->date, $startDay);
                $endDate = $this->getNextNthIncludingCurrent($this->date, $endDay);

                if(in_array($frequency->timePeriodPreset->name, ['05th_cut_off', '10th_cut_off'])){

                    $payrollYear = $startDate->year;
                    $payrollMonth = $startDate->month;
                }

                if(in_array($frequency->timePeriodPreset->name, ['20th_cut_off', '25th_cut_off'])){

                    $payrollYear = $endDate->year;
                    $payrollMonth = $endDate->month;
                }
            }
        }

        if($frequency->type == PayFrequencyEnum::SEMI_MONTHLY){

            if($frequency->timePeriodPreset->name == 'end_of_month_cut_off'){

                $firstHalfStartDay = $this->date->copy()->startOfMonth()->day;
                $firstHalfEndDay = (int)collect($frequency->period->cast)->where('key', '1st_half_end_date')->first()->value->day;
                $secondHalfStartDay = (int)collect($frequency->period->cast)->where('key', '2nd_half_start_date')->first()->value->day;
                $secondHalfEndDay = $this->date->copy()->endOfMonth()->day;

                if($this->date->day >= $secondHalfStartDay){
                    $startDate = $this->date->copy()->day($secondHalfStartDay);
                    $endDate = $this->date->copy()->day($secondHalfEndDay);
                    $frequencySequence = SemiMonthlySequence::SECOND_HALF;
                } else {
                    $startDate = $this->date->copy()->day($firstHalfStartDay);
                    $endDate = $this->date->copy()->day($firstHalfEndDay);
                    $frequencySequence = SemiMonthlySequence::FIRST_HALF;
                }

                $payrollYear = $startDate->year;
                $payrollMonth = $startDate->month;

            } else {

                $firstHalfStartDay = (int)collect($frequency->period->cast)->where('key', '1st_half_start_date')->first()->value->day;
                $firstHalfEndDay = (int)collect($frequency->period->cast)->where('key', '1st_half_end_date')->first()->value->day;
                $secondHalfStartDay = (int)collect($frequency->period->cast)->where('key', '2nd_half_start_date')->first()->value->day;
                $secondHalfEndDay = (int)collect($frequency->period->cast)->where('key', '2nd_half_end_date')->first()->value->day;

                if(in_array($frequency->timePeriodPreset->name, ['05th_cut_off', '10th_cut_off'])){

                    if($this->date->day >= $firstHalfStartDay && $this->date->day <= $firstHalfEndDay){
                        $startDate = $this->getPreviousNthIncludingCurrent($this->date, $firstHalfStartDay);
                        $endDate = $this->getNextNthIncludingCurrent($startDate, $firstHalfEndDay);
                        $frequencySequence = SemiMonthlySequence::FIRST_HALF;
                    } else {
                        $startDate = $this->getPreviousNthIncludingCurrent($this->date, $secondHalfStartDay);
                        $endDate = $this->getNextNthIncludingCurrent($startDate, $secondHalfEndDay);
                        $frequencySequence = SemiMonthlySequence::SECOND_HALF;
                    }

                    $payrollYear = $startDate->year;
                    $payrollMonth = $startDate->month;
                }

                if(in_array($frequency->timePeriodPreset->name, ['20th_cut_off', '25th_cut_off'])){

                    if($this->date->day >= $secondHalfStartDay && $this->date->day <= $secondHalfEndDay){
                        $startDate = $this->getPreviousNthIncludingCurrent($this->date, $secondHalfStartDay);
                        $endDate = $this->getNextNthIncludingCurrent($startDate, $secondHalfEndDay);
                        $frequencySequence = SemiMonthlySequence::SECOND_HALF;
                    } else {
                        $startDate = $this->getPreviousNthIncludingCurrent($this->date, $firstHalfStartDay);
                        $endDate = $this->getNextNthIncludingCurrent($startDate, $firstHalfEndDay);
                        $frequencySequence = SemiMonthlySequence::FIRST_HALF;
                    }

                    $payrollYear = $endDate->year;
                    $payrollMonth = $endDate->month;
                }
            }
        }

        return App::make(PayrollPayloadRepository::class)->hydrateItem([
            'year' => $payrollYear,
            'month' => $payrollMonth,
            'pay_frequency' => $frequency->type->value,
            'frequency_sequence' => $frequencySequence?->value,
            'start' => $startDate,
            'end' => $endDate,
        ]);
    }

    /**
     * @throws UnexpectedException
     */
    public function generateSalaryStatements(Payroll $payroll)
    {
        $debugEnabled = true;

        $this->payroll = $payroll;

        //Set company formula settings
        $this->resolveCompanyFormulaSettings();

        //Set company night hours
        $this->resolveCompanyNightHoursFromBasicPayFormulaSettings();

        $this->payroll->salaryStatements()->delete();

        $payFrequency = app(PayFrequencyRepository::class)->model()::query()
            ->where('company_id', $this->payroll->company_id)
            ->where('type', $this->payroll->pay_frequency->value)
            ->first();

        $filters = (object)[
            'company_id' => $this->payroll->company_id,
            'employee_ids' => [4],
            'pay_frequency_ids' => [$payFrequency->id],
        ];

        //Employee payroll frequency group
        $employees = app(EmployeeRepository::class)->queryBuilderCursor($filters);

        foreach($employees as $employee){

            $salaryStatement = app(SalaryStatementRepository::class)->model()::create([
                'payroll_id' => $this->payroll->id,
                'employee_id' => $employee->id,
            ]);

            $employee = app(EmployeeRepository::class)->hydrateItem($employee);
            $employeeShift = $employee->shifts->first();

            $salaryStatementAttendancesArray = $this->buildEmployeeSalaryStatementAttendances($employee, $employeeShift);

            foreach($salaryStatementAttendancesArray as $salaryStatementAttendanceArray){

                $salaryStatementAttendance = $salaryStatement->salaryStatementAttendances()->create($salaryStatementAttendanceArray);

                if($salaryStatementAttendance->status == SalaryStatementAttendanceStatus::DAY_OFF) continue;

                $attendanceArray = $salaryStatementAttendance->attendance?->toArray();
                $attendanceDetailsArray = $salaryStatementAttendance->attendance?->details?->toArray();

                /**
                 * If none-attendance but pay needed, create a schedule breakdown as a rate multiplier reference
                 **/
                $leaveWithoutPay = $salaryStatementAttendance->status == SalaryStatementAttendanceStatus::LEAVE_WITHOUT_PAY;
                $leaveWithPay = $salaryStatementAttendance->status == SalaryStatementAttendanceStatus::LEAVE_WITH_PAY;
                $isLegalHoliday = in_array($salaryStatementAttendance->day_type, [SalaryStatementAttendanceDayType::LEGAL_HOLIDAY, SalaryStatementAttendanceDayType::DOUBLE_HOLIDAY,]);
                $leaveWithoutPayAndIsLegalHoliday = $leaveWithoutPay && $isLegalHoliday;

                $isAbsentAndLegalHoliday = $salaryStatementAttendance->status == SalaryStatementAttendanceStatus::ABSENT && $isLegalHoliday;

                $payableNoneAttendance = $leaveWithPay || $leaveWithoutPayAndIsLegalHoliday || $isAbsentAndLegalHoliday;

                if($payableNoneAttendance){

                    $this->createSalaryStatementAttendanceDetails($employeeShift, $salaryStatementAttendance);

                }
            }
        }

        /**
         * Company per day-able compensations: (Basic pay, Allowance, Overtime)
         **/
        $companyPerDayAbleEarnings = $this->company->compensations->where('assignable', true)
            ->where('formulable_type', Formulable::EARNINGS->value)
            ->whereIn('type', [CompensationEnum::BASIC_PAY, CompensationEnum::REGULAR_ALLOWANCE, CompensationEnum::OVERTIME]);
        $companyPerDayAbleEarningsMorphFilterSlugs = $companyPerDayAbleEarnings
            ->map(fn($companyPerDayEarning) => $companyPerDayEarning->id . '.compensation')
            ->values()
            ->toArray();

        /**
         * Company global compensations: (Leave pay, Holiday pay)
         **/
        $companyPerDayAbleGlobalCompensations = $this->company->compensations
            ->where('assignable', false)
            ->where('formulable_type', Formulable::EARNINGS->value)
            ->whereIn('type', [CompensationEnum::HOLIDAY_PAY, CompensationEnum::LEAVE_PAY])
            ->sortBy('order');

        foreach($payroll->salaryStatements()->cursor() as $salaryStatement) {

            /**
             * Count all working days from salary statement attendance
             **/
            $this->frequencyWorkingDayCount = $salaryStatement->salaryStatementAttendances
                ->where('status', '!==', SalaryStatementAttendanceStatus::DAY_OFF->value)
                ->count();

            $employee = $salaryStatement->employee;

            foreach($salaryStatement->salaryStatementAttendances()->cursor() as $salaryStatementAttendance){

                /**
                 * In debug mode, limit date/s using date presets
                 **/
                if($debugEnabled && !in_array($salaryStatementAttendance->date->toDateString(), static::datePresets())) continue;

                $employeePayrollComponentFilters = (object)[
                    'employee_ids' => [$employee->id],
                    'payroll_componentable_type' => [Relation::getMorphAlias(Compensation::class)],
                    'payroll_componentable_morph' => $companyPerDayAbleEarningsMorphFilterSlugs,
                    'payroll_componentable_date' => $salaryStatementAttendance->date->toDateString()
                ];
                $employeePerDayableCompensations = app(EmployeePayrollComponentRepository::class)->list($employeePayrollComponentFilters);

                /**
                 * Create pay items for each salary statement attendance
                 **/
                $this->createSalaryStatementAttendancePayItems(
                    $salaryStatementAttendance,
                    $employee->shifts->first(),
                    $employeePerDayableCompensations,
                    $companyPerDayAbleGlobalCompensations
                );
            }
        }
    }

    public static function datePresets(): array
    {
        return [
            //Cut off 2026-01-26-2026-02-25
            //'2026-01-26',//FULL PRESENT NON-REST WITH OT
            //'2026-01-27',//FULL PRESENT NON-REST

            //'2026-01-28',//LWP REGULAR DAY
            //'2026-01-29',//LWOP

            //'2026-01-31',//FULL PRESENT REST DAY WITH OT

            //'2026-02-02',//LWP SPECIAL HOLIDAY
            //'2026-02-03',//LWOP SPECIAL HOLIDAY

            //'2026-02-05',//LWP LEGAL HOLIDAY
            //'2026-02-06',//LWOP LEGAL HOLIDAY

            //'2026-02-07',//FULL PRESENT REST DAY LEGAL HOLIDAY WITH OT
            //'2026-02-09',//ABSENT LEGAL HOLIDAY
            //'2026-02-10',//FULL PRESENT REGULAR DAY LEGAL HOLIDAY
            //'2026-02-11',//ABSENT REGULAR DAY SPECIAL HOLIDAY
            //'2026-02-12',//FULL PRESENT REGULAR DAY SPECIAL HOLIDAY WITH OT
            //'2026-02-13',//ABSENT REGULAR DAY
            //'2026-02-14',//FULL PRESENT REST DAY DOUBLE HOLIDAY WITH OT
            //'2026-02-17',//ABSENT LEGAL HOLIDAY

            //'2026-02-19',//LWP DOUBLE HOLIDAY
            //'2026-02-20',//LWOP DOUBLE HOLIDAY

            //'2026-02-21',//FULL PRESENT REST DAY SPECIAL HOLIDAY WITH OT
            //'2026-02-24',//FULL PRESENT REGULAR DAY DOUBLE HOLIDAY WITH OT
            //'2026-02-25',//ABSENT REGULAR DAY DOUBLE HOLIDAY

            //Cut off 2026-02-26-2026-03-25
            //'2026-02-26',//ABSENT LEGAL HOLIDAY
            //'2026-02-27',//ABSENT LEGAL HOLIDAY
            //'2026-02-28',//FULL PRESENT REST DAY REGULAR DAY
            //'2026-03-02',//ABSENT REGULAR DAY
            //'2026-03-03',//ABSENT REGULAR DAY
            //'2026-03-04',//ABSENT LEGAL HOLIDAY FORFEITED
            //'2026-03-05',//LWP LEGAL HOLIDAY
            //'2026-03-06',//ABSENT REGULAR DAY
            //'2026-03-07',//ABSENT SPECIAL HOLIDAY
            //'2026-03-09',//ABSENT LEGAL HOLIDAY FORFEITED

            //'2026-03-11',//LWP SPECIAL HOLIDAY
            //'2026-03-12',//ABSENT LEGAL HOLIDAY
            //'2026-03-13',//LWP SPECIAL HOLIDAY
            //'2026-03-14',//ABSENT LEGAL HOLIDAY
            //'2026-03-16',//ABSENT LEGAL HOLIDAY FORFEITED
            //'2026-03-17',//ABSENT LEGAL HOLIDAY FORFEITED
            //'2026-03-18',//ABSENT DOUBLE HOLIDAY FORFEITED
            //'2026-03-19',//ABSENT REGULAR DAY
            //'2026-03-20',//ABSENT LEGAL HOLIDAY FORFEITED
            //'2026-03-21',//ABSENT DOUBLE HOLIDAY FORFEITED
            //'2026-03-23',//LWP REGULAR DAY
            //'2026-03-24',//ABSENT LEGAL HOLIDAY
            //'2026-03-25',//ABSENT DOUBLE HOLIDAY
        ];
    }

    /**
     * @throws UnexpectedException
     */
    public function createSalaryStatementAttendancePayItems(
        SalaryStatementAttendance $salaryStatementAttendance,
        Shift $employeeShift,
        Collection $employeePerDayableCompensations,
        Collection $companyPerDayAbleGlobalCompensations): void
    {
        $debugEnabled = false;

        $date = $salaryStatementAttendance->date;

        list($isPresent, $isLeave, $isHoliday, $isDoubleHoliday, $isRegularWorkingDay, $isLegalHoliday, $isSpecialHoliday, $leaveWithoutPay, $leaveWithPay, $leaveWithoutPayAndIsLegalHoliday, $isAbsentAndLegalHoliday, $payableNoneAttendance)
            = $this->listSalaryStatementAttendanceStatusAndDayTypes($salaryStatementAttendance);

        $payrollFrequency = $this->payroll->pay_frequency;

        $this->setShift($employeeShift);
        $this->setAttendanceSchedule($date);

        //Get total work hours for the day
        $hasAttendance = boolval($salaryStatementAttendance->attendance);
        $attendanceDetails = $hasAttendance ? $salaryStatementAttendance->attendance->details : $salaryStatementAttendance->details;
        $totalWorkMinutes = $attendanceDetails->where('split_type', ShiftBreakDownSplitType::WORK->value)->sum('split_duration');

        if($debugEnabled){
            _debug([
                'attendance details total work minutes' => $totalWorkMinutes,
            ]);
        }

        if(!empty($attendanceDetails?->toArray())){

            if($debugEnabled){
                _debug([
                    'payroll frequency' => $payrollFrequency,
                    'payroll working day count' => $this->frequencyWorkingDayCount,
                ]);
            }

            /**
             * Employee assigned compensations (by attendance if amountable)
             **/
            $employeePerDayableCompensations = $employeePerDayableCompensations->filter(function ($compensation){
                $payrollComponentIsAmountable = in_array($compensation->payrollComponentable->type, [
                    CompensationEnum::BASIC_PAY,
                    CompensationEnum::REGULAR_ALLOWANCE
                ]);

                $payTypeIsByAttendance = $compensation->pay_type == PayType::BY_ATTENDANCE;

                return !$payrollComponentIsAmountable || $payTypeIsByAttendance;
            });

            /**
             * Limit to basic pay if no attendance but is payable by basic pay
             **/
            $employeePerDayableCompensations = (!$isPresent && $payableNoneAttendance) ? $employeePerDayableCompensations
                ->filter(function ($compensation){
                    return ($compensation->payrollComponentable->type == CompensationEnum::BASIC_PAY);
                }) : $employeePerDayableCompensations;

            /**
             * Payable split mapper
             **/
            $employeePerDayableCompensationsPayload = $employeePerDayableCompensations
                ->mapWithKeys(fn ($compensation) => [
                    $compensation->payrollComponentable->type->value => [
                        'hourly_rate' => null,
                        'work_hour_type' => WorkHourType::REGULAR->value,
                        'regular_pay' => 0,
                        'night_differential_pay' => 0,
                        'rest_day_pay' => 0,
                        'total' => 0,
                    ],
                ])
                ->all();

            /**
             * Payable split mapper
             **/
            $companyPerDayAbleGlobalCompensationsPayload = $companyPerDayAbleGlobalCompensations
                ->filter(function ($globalCompensation) use($isLeave, $isHoliday){
                    return ($isLeave && $globalCompensation->type == CompensationEnum::LEAVE_PAY) ||
                        ($isHoliday && $globalCompensation->type == CompensationEnum::HOLIDAY_PAY);
                })
                ->mapWithKeys(fn ($globalCompensation) => [
                    $globalCompensation->type->value => [
                        'hourly_rate' => null,
                        'work_hour_type' => WorkHourType::REGULAR->value,
                        'regular_pay' => 0,
                        'night_differential_pay' => 0,
                        'rest_day_pay' => 0,
                        'total' => 0,
                    ],
                ])
                ->all();

            if($debugEnabled){
                _debug([
                    'employee per dayable compensations' => $employeePerDayableCompensations->toArray(),
                    'employee per dayable compensations payload' => $employeePerDayableCompensationsPayload,
                    'global per dayable compensations payload' => $companyPerDayAbleGlobalCompensationsPayload,
                ]);
            }

            foreach($employeePerDayableCompensations as $employeePerDayableCompensation){

                if($this->frequencyWorkingDayCount < 1){
                    throw new UnexpectedException("Frequency working day count invalid: C.PayrollService@createSalaryStatementAttendancePayItems [" . __LINE__ . "]");
                }

                if($totalWorkMinutes < 1){
                    throw new UnexpectedException("Total shift split count invalid: C.PayrollService@createSalaryStatementAttendancePayItems [" . __LINE__ . "]");
                }

                $payrollComponentIsAmountable = in_array($employeePerDayableCompensation->payrollComponentable->type, [
                    CompensationEnum::BASIC_PAY,
                    CompensationEnum::REGULAR_ALLOWANCE
                ]);

                if($debugEnabled){
                    _debug([
                        'class' => get_class($employeePerDayableCompensation),
                        'formulable_type' => $employeePerDayableCompensation->formulable_type?->label(),
                        'payroll componentable type' => $employeePerDayableCompensation->payrollComponentable->type?->label(),
                        'payroll componentable amountable' => $payrollComponentIsAmountable,
                        'amount' => $employeePerDayableCompensation->amount,
                        'pay period' => $employeePerDayableCompensation->pay_period,
                    ]);
                }

                /**
                 * Get all amountable hourly rates from basic pay and allowance
                 **/
                if($payrollComponentIsAmountable){
                    switch($employeePerDayableCompensation->payrollComponentable->type){
                        case CompensationEnum::BASIC_PAY:
                            if(isset($employeePerDayableCompensationsPayload[CompensationEnum::BASIC_PAY->value])){
                                $employeePerDayableCompensationsPayload[CompensationEnum::BASIC_PAY->value]['hourly_rate'] +=
                                    $this->getAssignedPayrollComponentHourlyRate($payrollFrequency, $employeePerDayableCompensation, $totalWorkMinutes);
                            }break;
                        case CompensationEnum::REGULAR_ALLOWANCE:
                            if(isset($employeePerDayableCompensationsPayload[CompensationEnum::REGULAR_ALLOWANCE->value])){
                                $employeePerDayableCompensationsPayload[CompensationEnum::REGULAR_ALLOWANCE->value]['hourly_rate'] +=
                                    $this->getAssignedPayrollComponentHourlyRate($payrollFrequency, $employeePerDayableCompensation, $totalWorkMinutes);
                            }
                    }
                }
            }

            /**
             * Set basic pay hourly rate as global amountable hourly rate
             **/
            foreach($companyPerDayAbleGlobalCompensations as $companyPerDayAbleGlobalCompensation){
                switch($companyPerDayAbleGlobalCompensation->type){
                    case CompensationEnum::LEAVE_PAY:
                        if($isLeave &&
                            isset($employeePerDayableCompensationsPayload[CompensationEnum::BASIC_PAY->value]['hourly_rate']) &&
                            isset($companyPerDayAbleGlobalCompensationsPayload[CompensationEnum::LEAVE_PAY->value])
                        ){
                            $companyPerDayAbleGlobalCompensationsPayload[CompensationEnum::LEAVE_PAY->value]['hourly_rate'] =
                                $employeePerDayableCompensationsPayload[CompensationEnum::BASIC_PAY->value]['hourly_rate'];
                        }
                        break;
                    case CompensationEnum::HOLIDAY_PAY:
                        if($isHoliday &&
                            isset($employeePerDayableCompensationsPayload[CompensationEnum::BASIC_PAY->value]['hourly_rate']) &&
                            isset($companyPerDayAbleGlobalCompensationsPayload[CompensationEnum::HOLIDAY_PAY->value])
                        ){
                            $companyPerDayAbleGlobalCompensationsPayload[CompensationEnum::HOLIDAY_PAY->value]['hourly_rate'] =
                                $employeePerDayableCompensationsPayload[CompensationEnum::BASIC_PAY->value]['hourly_rate'];
                        }
                        break;
                }
            }

            $workSplitCollection = $attendanceDetails->where('split_type', ShiftBreakDownSplitType::WORK->value);
            $overtimeSplitCollection = $attendanceDetails->where('split_type', ShiftBreakDownSplitType::OVERTIME->value);

            $payableSplitTransformer = $hasAttendance
                ? AttendanceDetailPayableSplitTransformer::class
                : SalaryStatementAttendanceDetailPayableSplitTransformer::class;

            $this->workSplits = Fractal::collection($workSplitCollection, $payableSplitTransformer)['data'];
            $this->overtimeSplits = Fractal::collection($overtimeSplitCollection, $payableSplitTransformer)['data'];

            $this->statementAttendanceSetAmountableOnSplits(
                $salaryStatementAttendance,
                $employeePerDayableCompensationsPayload,
                $companyPerDayAbleGlobalCompensationsPayload,
                false,
                true
            );

            /**
             * Create pay items
             **/
            $employeePerDayableByAttendanceCompensationsPatchable = collect($employeePerDayableCompensationsPayload)->map(fn($value, $key) => [
                'formulable_type' => Formulable::EARNINGS->value,
                'component_type' => $key,
                'work_hour_type' => $value['work_hour_type'],
                'regular_pay' => $value['regular_pay'],
                'night_differential_pay' => $value['night_differential_pay'],
                'rest_day_pay' => $value['rest_day_pay'],
                'total' => $value['total'],
            ])->values()->toArray();

            $companyPerDayAbleGlobalCompensationsPatchable = collect($companyPerDayAbleGlobalCompensationsPayload)->map(fn($value, $key) => [
                'formulable_type' => Formulable::EARNINGS->value,
                'component_type' => $key,
                'work_hour_type' => $value['work_hour_type'],
                'regular_pay' => $value['regular_pay'],
                'night_differential_pay' => $value['night_differential_pay'],
                'rest_day_pay' => $value['rest_day_pay'],
                'total' => $value['total'],
            ])->values()->toArray();

            $attendancePayItemsPatchable = array_merge($employeePerDayableByAttendanceCompensationsPatchable, $companyPerDayAbleGlobalCompensationsPatchable);

            _debug([
                'Attendance pay items' => $attendancePayItemsPatchable,
            ]);

            foreach($attendancePayItemsPatchable as $attendancePayItem){
                $salaryStatementAttendance->payrollComponents()->create($attendancePayItem);
            }
        }
    }

    /**
     * @throws UnexpectedException
     */
    public function createSalaryStatementAttendanceDetails(Shift $employeeShift, SalaryStatementAttendance $salaryStatementAttendance): void
    {
        $debugEnabled = false;

        $date = $salaryStatementAttendance->date;

        $this->setShift($employeeShift);
        $this->setAttendanceSchedule($date);

        $startingDateHolidayType = $this->getDateHolidayType($date);
        $startingDateIsRestDay = in_array($date->dayOfWeek, $this->restDays);

        $schedule = $this->attendanceSchedule;
        $schedule = $this->parseSchedule($schedule, $salaryStatementAttendance->date);

        $workPeriods = $this->calculateWorkPeriods($schedule);

        list($scheduleBreakdown) = $this->breakdownWorkPeriods($workPeriods, $startingDateIsRestDay, $startingDateHolidayType, [ShiftBreakDownSplitType::WORK]);

        if($debugEnabled){
            _debug([
                'date' => $date->toDateString(),
                'shift class' => get_class($employeeShift),
                'salary statement class' => get_class($salaryStatementAttendance),
                'status' => $salaryStatementAttendance->status?->label(),
                'day_type' => $salaryStatementAttendance->day_type?->label(),
                '$scheduleBreakdown' => $scheduleBreakdown,
            ]);
        }

        foreach($scheduleBreakdown as $scheduleBreakdownItem){

            $salaryStatementAttendance->details()->create($scheduleBreakdownItem);
        }
    }

    /**
     * @throws UnexpectedException
     */
    public function buildEmployeeSalaryStatementAttendances( Employee $employee, ?Shift $employeeShift): array
    {
        $debugEnabled = false;

        //Payroll date period
        $datePeriod = CarbonPeriod::create($this->payroll->start_date, $this->payroll->end_date);

        //Build employee's salary attendance
        $salaryStatementAttendances = [];

        $employeeDatePeriodAttendances = app(AttendanceRepository::class)
            ->model()::where('employee_id', $employee->id)
            ->whereBetween('date', [$datePeriod->start->toDateString(), $datePeriod->end->toDateString()])
            ->get();
        $employeeDatePeriodLeaves = app(LeaveRepository::class)
            ->model()::where('employee_id', $employee->id)
            ->whereBetween('date', [$datePeriod->start->toDateString(), $datePeriod->end->toDateString()])
            ->get();

        $employeeDatePeriodAttendances = Fractal::collection(
            $employeeDatePeriodAttendances,
            AttendancePatchableTransformer::class
        )['data'];

        $employeeDatePeriodLeaves = Fractal::collection(
            $employeeDatePeriodLeaves,
            LeaveBasicTransformer::class
        )['data'];

        foreach($datePeriod as $date){

            if(empty($employeeShift)) continue;

            $attendance = collect($employeeDatePeriodAttendances)->where('date', $date->toDateString())->first();
            $attendance = $attendance ? app(AttendanceRepository::class)->hydrateItem($attendance) : null;

            $leave = collect($employeeDatePeriodLeaves)->where('date', $date->toDateString());
            $hasLeave = $leave->isNotEmpty();
            $leaveType = null;

            if($hasLeave){
                $leaveType = app(LeaveTypeRepository::class)->model()::find($leave->first()['leave_type']['id']);
            }

            if($debugEnabled){
                _debug([
                    'date' => $date->toDateString(),
                    '$attendance status' => $attendance?->status?->label(),
                    '$hasLeave' => $hasLeave,
                    '$leaveType is_paid' => $leaveType?->is_paid,
                ]);
            }

            $this->setShift($employeeShift);
            $this->setAttendanceSchedule($date);
            $dayOff = $this->attendanceScheduleIsDayOff;
            $holiday = $this->getCompanyHolidayByDate($date, $this->company->id);
            $holidayType = !empty($holiday) ? $holiday->type : null;
            $this->holidayPayForfeiture = !empty($holiday) ? $holiday->holiday_pay_forfeiture : false;

            $isDateIsHoliday = !empty($holidayType);
            $shiftHolidayPolicyIsDayOff = $this->shiftHolidayPolicy == ShiftHolidayPolicy::DAY_OFF;
            $dayOffOrHoliday = $dayOff || ($shiftHolidayPolicyIsDayOff && $isDateIsHoliday);

            $dayType = $dayOffOrHoliday ? SalaryStatementAttendanceDayType::DAY_OFF : SalaryStatementAttendanceDayType::WORKING_DAY;
            $dayType = $isDateIsHoliday
                ? match($holidayType){
                    HolidayType::SPECIAL => SalaryStatementAttendanceDayType::SPECIAL_HOLIDAY,
                    HolidayType::LEGAL => SalaryStatementAttendanceDayType::LEGAL_HOLIDAY,
                    HolidayType::DOUBLE => SalaryStatementAttendanceDayType::DOUBLE_HOLIDAY,
                } : $dayType;

            if(empty($attendance) && $dayOffOrHoliday){
                $payrollAttendanceStatus = SalaryStatementAttendanceStatus::DAY_OFF;
            } else if(empty($attendance) && !$hasLeave) {
                $payrollAttendanceStatus = SalaryStatementAttendanceStatus::ABSENT;
            } else if ($hasLeave){
                $payrollAttendanceStatus = match($leaveType?->is_paid){
                    true => SalaryStatementAttendanceStatus::LEAVE_WITH_PAY,
                    false => SalaryStatementAttendanceStatus::LEAVE_WITHOUT_PAY,
                    null => SalaryStatementAttendanceStatus::LEAVE_BUT_CANT_IDENTIFY_IF_PAID_OR_NOT,
                };
            } else {
                $payrollAttendanceStatus = match($attendance->status){
                    AttendanceStatus::FULL_PRESENT => SalaryStatementAttendanceStatus::FULL_PRESENT,
                    AttendanceStatus::PRESENT_WITH_IRREGULARITIES => SalaryStatementAttendanceStatus::PRESENT_WITH_IRREGULARITIES,
                    AttendanceStatus::ABSENT => SalaryStatementAttendanceStatus::ABSENT,
                    null => SalaryStatementAttendanceStatus::TO_BE_DETERMINED,
                };
            }

            $salaryStatementAttendance = [
                ...(false ? [
                    '_employee_id' => $employee->id,
                    '_is_day_off' => $dayOff,
                    '_is_holiday' => $isDateIsHoliday,
                    '_is_day_off_and_holiday_day_off' => $dayOffOrHoliday,
                    '_holiday_type' => $holidayType,
                    '_shift_holiday_policy_is_day_off' => $shiftHolidayPolicyIsDayOff,
                ] : []),
                'attendance_id' => $attendance?->id ?? null,
                'date' => $date->toDateString(),
                'status' => $payrollAttendanceStatus->value,
                'day_type' => $dayType->value,
            ];

            if($debugEnabled){
                _debug([
                    '$salaryStatementAttendance' => [
                        'attendance_id' => $attendance?->id ?? null,
                        'date' => $date->toDateString(),
                        'status' => $payrollAttendanceStatus->label(),
                        'day_type' => $dayType->label(),
                    ],
                ]);
            }

            $salaryStatementAttendances[] = $salaryStatementAttendance;
        }

        return $salaryStatementAttendances;
    }
}
