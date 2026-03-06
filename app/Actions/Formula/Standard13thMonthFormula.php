<?php

namespace App\Actions\Formula;

use App\Blueprint\PayrollServiceInterface;
use App\Blueprint\Repositories\EmployeePayrollComponentRepository;
use App\Blueprint\Repositories\LeaveRepository;
use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Concrete\SalaryStatementContext;
use App\Concrete\SalaryStatementModuleServiceConcrete;
use App\Enums\Compensation as CompensationEnum;
use App\Enums\Deduction;
use App\Enums\Formulable;
use App\Enums\FormulableComponentSubType;
use App\Enums\HolidayType;
use App\Enums\PayFrequency;
use App\Enums\PayType;
use App\Enums\SalaryStatementDetailComponentValueType;
use App\Enums\SemiMonthlySequence;
use App\Enums\ShiftBreakDownSplitType;
use App\Enums\ShiftHolidayPolicy;
use App\Exceptions\UnexpectedException;
use App\Facades\Fractal;
use App\Models\Company;
use App\Models\Compensation;
use App\Models\Employee;
use App\Models\Payroll;
use App\Traits\HasPayableDay;
use App\Traits\WorkPeriod;
use App\Transformers\Leave\BasicTransformer as LeaveBasicTransformer;
use App\Transformers\PayrollPayload\ListTransformer as PayrollPayloadListTransformer;
use Brick\Math\BigDecimal;
use Brick\Math\BigInteger;
use Brick\Math\RoundingMode;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class Standard13thMonthFormula
{
    public SalaryStatementContext $context;

    protected ?Company $company;
    public int $frequencyWorkingDayCount = 0;

    public string $slug = 'standard-13th-month';

    public int $formulaSettingsPayrollMonth = 0;
    public ?SemiMonthlySequence $formulaSettingsPayrollMonthSequence = null;
    public int $formulaSettingsReconcilePayrollMonth = 0;
    public ?SemiMonthlySequence $formulaSettingsReconcilePayrollSequence = null;

    public bool $prorate13thMonth = false;

    public ?BigDecimal $taxExempt;

    use WorkPeriod, HasPayableDay;

    /**
     * @throws UnexpectedException
     */
    public function handle(SalaryStatementContext $context, $next)
    {
        $debugEnabled = false;
        $this->context = $context;
        $pipelinePayload = $context->pipelinePayload->where('formula_slug', $this->slug)->first();
        $formulableModel = $pipelinePayload['formulable_model'];
        $companyFormula = $formulableModel->companyFormula;
        $formulaSettings = $companyFormula->settings;
        $formula = $pipelinePayload['formula'];

        $this->formulaSettings($formulaSettings->cast, $context->payroll->pay_frequency, $context->payroll->frequency_sequence);

        $payrollMonthTrigger = $this->formulaSettingsPayrollMonth == $context->payroll->month &&
            $this->formulaSettingsPayrollMonthSequence == $context->payroll->frequency_sequence;

        $reconcilePayrollMonthTrigger = $this->formulaSettingsReconcilePayrollMonth == $context->payroll->month &&
            $this->formulaSettingsReconcilePayrollSequence == $context->payroll->frequency_sequence;

        /**
         * Reconciliation trigger cannot be the same as payroll month trigger
         **/
        if($this->context->payroll->pay_frequency == PayFrequency::MONTHLY){

            $reconcilePayrollMonthTrigger = $this->formulaSettingsPayrollMonthSequence == $this->formulaSettingsReconcilePayrollMonth
                ? false
                : $reconcilePayrollMonthTrigger;

        } else if($this->context->payroll->pay_frequency == PayFrequency::SEMIMONTHLY){

            $reconcilePayrollMonthTrigger = $this->formulaSettingsPayrollMonthSequence == $this->formulaSettingsReconcilePayrollMonth &&
                $this->formulaSettingsReconcilePayrollSequence == $this->formulaSettingsPayrollMonthSequence
                ? false
                : $reconcilePayrollMonthTrigger;
        }

        $isFinalPayState = $this->context->isFinalPayState;

        $this->prorate13thMonth = !$this->context->isPayrollYearEnd && $isFinalPayState;

        if (true || $debugEnabled) {

            _debug([
                'Formula settings payroll month' => $this->formulaSettingsPayrollMonth,
                'Formula settings payroll month sequence' => $this->formulaSettingsPayrollMonthSequence,
                'Formula setting reconcile payroll month' => $this->formulaSettingsReconcilePayrollMonth,
                'Formula setting reconcile payroll month sequence' => $this->formulaSettingsReconcilePayrollSequence,
                'Payroll month trigger' => $payrollMonthTrigger,
                'Reconcile payroll month trigger' => $reconcilePayrollMonthTrigger,
                'Is final pay state' =>$isFinalPayState,
                'Prorate 13th month' => $this->prorate13thMonth,
            ]);
        }

        if (empty($this->formulaSettingsPayrollMonth) || empty($this->formulaSettingsReconcilePayrollMonth)) {

            return $next($context);
        }

        if ($payrollMonthTrigger || $reconcilePayrollMonthTrigger || $isFinalPayState) {

            if ($debugEnabled) {
                _debug([
                    'Formula slug' => $this->slug,
                    'Formulable' => get_class($formulableModel),
                    'Company formula' => get_class($companyFormula),
                    'Formula' => get_class($formula),
                    'Totals' => $context->totals,
                    'Formula settings' => $formulaSettings->cast,
                    'Statement details' => $context->statementDetails
                ]);
            }

            $payrollYearSalaryStatements = $this->context->getPayrollYearSalaryStatements($context->payroll, $context->employee);
            $actualTotalBasicGross = $this->context->getTotalFromSalaryStatementCollection($payrollYearSalaryStatements, 'total_basic_gross');
            $projectedBasicGross = BigDecimal::zero();

            /**
             * Project next payroll months, and add them up in $projectedBasicGross
             *
             * If not prorated, project the rest of calendar year,
             * Assume the next calendar months to be full attendance
             **/
            if ($payrollMonthTrigger && !$reconcilePayrollMonthTrigger && !$this->prorate13thMonth) {

                $nextPayrollBasicPayAssumptions = $this->projectBasicPayOfTheRestOfCalendarYear($context);

                foreach ($nextPayrollBasicPayAssumptions as $month => $projectedBasic) {

                    if ($debugEnabled) {
                        _debug([
                            'Projected basic pay of month ' . $month => $projectedBasic->toScale(2, RoundingMode::HalfUp)->toString() . ''
                        ]);
                    }

                    $projectedBasicGross = $projectedBasicGross->plus($projectedBasic);
                }
            }

            /**
             * Chain the payroll year total nontaxable nonstatutory bonus into context total nontaxable bonus
             **/
            $payrollYearNonTaxableNonstatutoryBonus = $this->context->getTotalFromSalaryStatementCollection($payrollYearSalaryStatements, 'total_nontaxable_nonstatutory_bonus');

            $totalNontaxableBonus = BigDecimal::of($context->totals['nontaxable_bonus'] ?? '0');
            $totalTaxableBonus = BigDecimal::of($context->totals['taxable_bonus'] ?? '0');

            list(
                $nextChainNontaxable,$subjectNontaxable,$subjectTaxable
            ) = $this->context->chainNontaxable($totalNontaxableBonus, $payrollYearNonTaxableNonstatutoryBonus,
                $totalNontaxableBonus, $totalTaxableBonus, $this->taxExempt, false);
            $totalNontaxableBonus = $subjectNontaxable;
            $totalTaxableBonus = $subjectTaxable;

            /**
             * Chain the nontaxable total of payroll year total nontaxable nonstatutory bonus and context total nontaxable bonus
             * into 13th month total
             **/
            $_13thMonth = $actualTotalBasicGross->plus($projectedBasicGross)
                ->dividedBy(BigDecimal::of('12'), 6, RoundingMode::HalfUp);
            $_13thMonthNonTaxable = BigDecimal::zero();
            $_13thMonthTaxable = BigDecimal::zero();

            list(
                $nextChainNontaxable,$subjectNontaxable,$subjectTaxable
            ) = $this->context->chainNontaxable($_13thMonth, $nextChainNontaxable,
                $_13thMonthNonTaxable, $_13thMonthTaxable, $this->taxExempt);
            $totalNontaxableBonus = $totalNontaxableBonus->plus($subjectNontaxable);
            $totalTaxableBonus = $totalTaxableBonus->plus($subjectTaxable);

            if (true || $debugEnabled) {

                _debug([
                    'Formula slug' => $this->slug,
                    'Employee' => $context->employee->full_name,
                    'Settings payroll month' => $this->formulaSettingsPayrollMonth,
                    'Reconcile payroll month' => $this->formulaSettingsReconcilePayrollMonth,
                    'Payroll year' => $context->payroll->year,
                    'Payroll month' => $context->payroll->month,
                    'Total actual basic gross' => $actualTotalBasicGross->toScale(2, RoundingMode::HalfUp)->toString(),
                    'Projected basic gross' => $projectedBasicGross->toScale(2, RoundingMode::HalfUp)->toString(),

                    'Payroll year nonstatutory nontaxable bonus' => $payrollYearNonTaxableNonstatutoryBonus->toScale(2, RoundingMode::HalfUp)->toString(),
                    'Total nontaxable bonus' => $totalNontaxableBonus->toScale(2, RoundingMode::HalfUp)->toString(),
                    'Total taxable bonus' => $totalTaxableBonus->toScale(2, RoundingMode::HalfUp)->toString(),
                    '13th month' => $_13thMonth->toScale(2, RoundingMode::HalfUp)->toString(),
                    '13th month non taxable' => $_13thMonthNonTaxable->toScale(2, RoundingMode::HalfUp)->toString(),
                    '13th month taxable' => $_13thMonthTaxable->toScale(2, RoundingMode::HalfUp)->toString(),
                ]);
            }

            /**
             * 13th month reconciliation is only performed when the 13th month projection is performed before year end payroll
             * On 13th month reconciliation, compare actual + projected 13th month value, to the actual 13th month value
             **/
            if ($reconcilePayrollMonthTrigger) {

                if (true || $debugEnabled) {

                    _debug([
                        '13th month Reconciliation' => 'Start',
                    ]);
                }

                $assumedActualWithProjected13thMonth = $this->context->getTotalFromSalaryStatementCollection($payrollYearSalaryStatements, 'total_13th_month_amount');
                $payrollYearNonstatutoryBonus = $this->context->getTotalFromSalaryStatementCollection($payrollYearSalaryStatements, 'total_nonstatutory_bonus');

                //Deduct the paid 13th month to the actual 13th month
                $adjustment = $_13thMonth->minus($assumedActualWithProjected13thMonth);

                /**
                 * If adjustment is negative, this means that the employee's 13th month is overpaid and needed negative adjustment
                 **/
                if($adjustment->isLessThan(BigDecimal::zero())){

                    $assumedActualWithProjected13thMonthWithNonstatutoryBonus = $assumedActualWithProjected13thMonth->plus($payrollYearNonstatutoryBonus);
                    $absoluteAdjustment = $adjustment->abs();
                    $nontaxableNegativeAdjustment = BigDecimal::zero();
                    $taxableNegativeAdjustment = BigDecimal::zero();

                    /**
                     * Check if some of the total bonus that are paid on 13th month trigger are taxable,
                     * If there is, then negative adjustment will have a max taxable negative adjustment of what's been over tax-exempt
                     **/
                    if ($assumedActualWithProjected13thMonthWithNonstatutoryBonus->isGreaterThan($this->taxExempt)) {

                        $taxableBonusExcess = $assumedActualWithProjected13thMonthWithNonstatutoryBonus->minus($this->taxExempt);

                        if($absoluteAdjustment->isGreaterThan($taxableBonusExcess)){

                            $taxableExcessAdjustment = $absoluteAdjustment->minus($taxableBonusExcess);
                            $taxableNegativeAdjustment = $taxableNegativeAdjustment->plus($taxableBonusExcess);

                            $nontaxableNegativeAdjustment = $nontaxableNegativeAdjustment->plus($taxableExcessAdjustment);

                        } else {

                            $taxableNegativeAdjustment = $taxableNegativeAdjustment->plus($absoluteAdjustment);
                        }

                    } else {

                        $nontaxableNegativeAdjustment = $nontaxableNegativeAdjustment->plus($absoluteAdjustment);
                    }

                    $negativeAdjustmentComponentValues = [
                        'type' => SalaryStatementDetailComponentValueType::PH_BONUS_13TH_MONTH_NEGATIVE_ADJUSTMENT->value,
                        '13th_month_projected' => $assumedActualWithProjected13thMonth->toScale(2, RoundingMode::HalfUp)->toString(),
                        '13th_month_actual' => $_13thMonth->toScale(2, RoundingMode::HalfUp)->toString(),
                        '13th_month_adjustment' => $adjustment->toScale(2, RoundingMode::HalfUp)->toString(),
                    ];

                    $taxableNegativeAdjustment = $taxableNegativeAdjustment->toScale(2, RoundingMode::HalfUp)->isGreaterthan(BigDecimal::zero())
                        ? $taxableNegativeAdjustment->negated()
                        : BigDecimal::zero();

                    if (true || $debugEnabled) {
                        _debug([
                            'Taxable negative adjustment' => $taxableNegativeAdjustment->toScale(2, RoundingMode::HalfUp)->toString(),
                            'Nontaxable negative adjustment' => $nontaxableNegativeAdjustment->toScale(2, RoundingMode::HalfUp)->toString(),
                        ]);
                    }

                    //Update total taxable
                    $totalTaxable = BigDecimal::of($context->totals['taxable'] ?? '0');
                    $context->totals = [
                        ...$context->totals,
                        'taxable' => $totalTaxable->plus($taxableNegativeAdjustment)->toScale(6, RoundingMode::HalfUp)->toString(),
                    ];

                    $context->statementDetails[] = [
                        'id' => null,
                        'formulable_type' => Formulable::DEDUCTIONS->value,
                        'component_type' => Deduction::THIRTEENTH_MONTH_ADJUSTMENT->value,
                        'component_sub_type' => FormulableComponentSubType::STATUTORY_BENEFIT_13TH_MONTH_NEGATIVE_ADJUSTMENT->value,
                        'component_name' => FormulableComponentSubType::STATUTORY_BENEFIT_13TH_MONTH_NEGATIVE_ADJUSTMENT->label(),
                        'component_values' => $negativeAdjustmentComponentValues,
                        'taxable' => $taxableNegativeAdjustment->toScale(2, RoundingMode::HalfUp)->toString(),
                        'nontaxable' => 0.0,
                        'deduction' => $nontaxableNegativeAdjustment->toScale(2, RoundingMode::HalfUp)->toString(),
                        'contribution' => 0.0,
                        'withholding_tax' => 0.0,
                        'net' => 0.0,
                    ];
                }

                /**
                 * If adjustment is positive, this means that the employee's 13th month is underpaid and needed positive adjustment
                 * */
                if($adjustment->toScale(2, RoundingMode::HalfUp)->isGreaterThan(BigDecimal::zero())){

                    $nontaxableAdjustment = BigDecimal::zero();
                    $taxableAdjustment = BigDecimal::zero();

                    $assumedActualWithProjected13thMonthWithNonstatutoryBonus = $assumedActualWithProjected13thMonth->plus($payrollYearNonstatutoryBonus);

                    if ($assumedActualWithProjected13thMonthWithNonstatutoryBonus->isGreaterThan($this->taxExempt)) {

                        $taxableAdjustment = $taxableAdjustment->plus($adjustment);

                    } else {

                        $calendarYearBonus = $assumedActualWithProjected13thMonthWithNonstatutoryBonus->plus($adjustment);

                        if ($calendarYearBonus->isGreaterThan($this->taxExempt)) {

                            $taxableBonusExcess = $calendarYearBonus->minus($this->taxExempt);
                            $nontaxableAdjustment = $adjustment->minus($taxableBonusExcess);

                            $taxableAdjustment = $taxableAdjustment->plus($taxableBonusExcess);

                        } else {

                            $nontaxableAdjustment = $nontaxableAdjustment->plus($adjustment);
                        }
                    }

                    if(
                        $taxableAdjustment->toScale(2, RoundingMode::HalfUp)->isGreaterThan(BigDecimal::zero()) ||
                        $nontaxableAdjustment->toScale(2, RoundingMode::HalfUp)->isGreaterThan(BigDecimal::zero())
                    ){
                        $positiveAdjustmentComponentValues = [
                            'type' => SalaryStatementDetailComponentValueType::PH_BONUS_13TH_MONTH_POSITIVE_ADJUSTMENT->value,
                            '13th_month_projected' => $assumedActualWithProjected13thMonth->toScale(2, RoundingMode::HalfUp)->toString(),
                            '13th_month_actual' => $_13thMonth->toScale(2, RoundingMode::HalfUp)->toString(),
                            '13th_month_adjustment' => $adjustment->toScale(2, RoundingMode::HalfUp)->toString(),
                        ];

                        if (true || $debugEnabled) {
                            _debug([
                                'Taxable positive adjustment' => $taxableAdjustment->toScale(2, RoundingMode::HalfUp)->toString(),
                                'Nontaxable positive adjustment' => $nontaxableAdjustment->toScale(2, RoundingMode::HalfUp)->toString(),
                            ]);
                        }

                        $context->statementDetails[] = [
                            'id' => null,
                            'formulable_type' => Formulable::EARNINGS->value,
                            'component_type' => CompensationEnum::THIRTEENTH_MONTH_ADJUSTMENT->value,
                            'component_sub_type' => FormulableComponentSubType::STATUTORY_BENEFIT_13TH_MONTH_POSITIVE_ADJUSTMENT->value,
                            'component_name' => FormulableComponentSubType::STATUTORY_BENEFIT_13TH_MONTH_POSITIVE_ADJUSTMENT->label(),
                            'component_values' => $positiveAdjustmentComponentValues,
                            'taxable' => $taxableAdjustment->toScale(2, RoundingMode::HalfUp)->toString(),
                            'nontaxable' => $nontaxableAdjustment->toScale(2, RoundingMode::HalfUp)->toString(),
                            'deduction' => 0.0,
                            'contribution' => 0.0,
                            'withholding_tax' => 0.0,
                            'net' => 0.0,
                        ];
                    }
                }

                if (true || $debugEnabled) {
                    _debug([
                        'Payroll year nonstatutory bonus' => $payrollYearNonstatutoryBonus->toScale(2, RoundingMode::HalfUp)->toString(),
                        'Assumed actual with projected 13th month' => $assumedActualWithProjected13thMonth->toScale(2, RoundingMode::HalfUp)->toString(),
                        'New 13th month with adjustment' => $_13thMonth->toScale(2, RoundingMode::HalfUp)->toString(),
                        'Adjustment' => $adjustment->toScale(2, RoundingMode::HalfUp)->toString(),
                    ]);
                }
            }

            /**
             * Create a 13th month triggered salary statement detail
             **/
            if (($payrollMonthTrigger || $isFinalPayState) && !$reconcilePayrollMonthTrigger) {

                //Update bonus totals
                $context->totals = [
                    ...$context->totals,
                    'nontaxable_bonus' => $totalNontaxableBonus->toScale(6, RoundingMode::HalfUp)->toString(),
                    'taxable_bonus' => $totalTaxableBonus->toScale(6, RoundingMode::HalfUp)->toString(),
                ];

                $statementDetail = [
                    'id' => null,
                    'formulable_type' => $formula->formulable_type->value,
                    'component_type' => $formula->component_type->value,
                    'component_sub_type' => FormulableComponentSubType::STATUTORY_BENEFIT_13TH_MONTH->value,
                    'component_name' => FormulableComponentSubType::STATUTORY_BENEFIT_13TH_MONTH->label(),
                    'component_values' => null,
                    'taxable' => $_13thMonthTaxable->toScale(6, RoundingMode::HalfUp)->toString(),
                    'nontaxable' => $_13thMonthNonTaxable->toScale(6, RoundingMode::HalfUp)->toString(),
                    'deduction' => 0.0,
                    'contribution' => 0.0,
                    'withholding_tax' => 0.0,
                    'net' => 0.0,
                ];

                $componentValues = [
                    'type' => SalaryStatementDetailComponentValueType::PH_BONUS_13TH_MONTH->value,
                    '13th_month_is_prorated' => $this->prorate13thMonth,
                    '13th_month_is_projected' => $projectedBasicGross->isGreaterThan(BigDecimal::zero()),
                    '13th_month_actual_basic_gross' => $actualTotalBasicGross->toScale(2, RoundingMode::HalfUp)->toString(),
                    '13th_month_projected_basic_gross' => $projectedBasicGross->toScale(2, RoundingMode::HalfUp)->toString(),
                    '13th_month_total_basic_gross' => $actualTotalBasicGross->plus($projectedBasicGross)->toScale(2, RoundingMode::HalfUp)->toString(),
                    '13th_month_amount' => $_13thMonth->toScale(2, RoundingMode::HalfUp)->toString(),
                ];

                $statementDetail['component_values'] = $componentValues;

                $context->statementDetails[] = $statementDetail;
            }
        }

        return $next($context);
    }

    public function formulaSettings($castedCompanyFormulaSettings, PayFrequency $payFrequency, ?SemiMonthlySequence $semiMonthlySequence): void
    {
        $settings = collect($castedCompanyFormulaSettings);
        $taxExempt = $settings->where('key', 'tax_exempt')->first()->value;
        $this->taxExempt = BigDecimal::of((string)$taxExempt);

        if(
            $payFrequency == PayFrequency::MONTHLY ||
            $this->context->flags['is_weekly_and_is_last_split_of_month']
        ){
            $monthlySchedule = $settings->where('key', 'monthly_schedule')->first()->value;
            $monthlySchedule = collect($monthlySchedule);
            $payrollMonth = $monthlySchedule->where('key', 'payroll_month')->first()->value;
            $reconcilePayrollMonth = $monthlySchedule->where('key', 'reconcile_payroll_month')->first()->value;

            $this->formulaSettingsPayrollMonth = $payrollMonth;
            $this->formulaSettingsReconcilePayrollMonth = $reconcilePayrollMonth;
        }

        if($payFrequency = PayFrequency::SEMIMONTHLY && !empty($semiMonthlySequence)){

            $semiMonthlySchedule = $settings->where('key', 'semimonthly_schedule')->first()->value;
            $semiMonthlySchedule = collect($semiMonthlySchedule);
            $payrollMonth = $semiMonthlySchedule->where('key', 'payroll_month')->first()->value;
            $payrollSemimonthSequence = $semiMonthlySchedule->where('key', 'payroll_month_sequence')->first()->value;
            $reconcilePayrollMonth = $semiMonthlySchedule->where('key', 'reconcile_payroll_month')->first()->value;
            $reconcilePayrollSemimonthSequence = $semiMonthlySchedule->where('key', 'reconcile_payroll_month_sequence')->first()->value;

            $this->formulaSettingsPayrollMonth = $payrollMonth;
            $this->formulaSettingsPayrollMonthSequence = SemiMonthlySequence::tryFrom($payrollSemimonthSequence);
            $this->formulaSettingsReconcilePayrollMonth = $reconcilePayrollMonth;
            $this->formulaSettingsReconcilePayrollSequence = SemiMonthlySequence::tryFrom($reconcilePayrollSemimonthSequence);
        }

    }

    public function projectBasicPayOfTheRestOfCalendarYear(SalaryStatementContext $context): array
    {
        $debugEnabled = false;

        $nextPayrollBasicPayAssumptions = [];

        $this->company = $context->company;
        //Set company formula settings
        $this->resolveCompanyFormulaSettings();
        //Set company night hours
        $this->resolveCompanyNightHoursFromBasicPayFormulaSettings();

        $payrollService = app(PayrollServiceInterface::class, [$context->company]);
        $payrollService->setCustomDate($context->payroll->end_date);
        $payrollPayFrequency = $context->payroll->pay_frequency;
        $currentUpToEndOfYear = $payrollService->getCurrentUpToEndOfYear($context->company->id, $context->payroll->year, [$payrollPayFrequency->value]);
        $nextPayrolls = Fractal::collection($currentUpToEndOfYear['next'], PayrollPayloadListTransformer::class)['data'];
        foreach($nextPayrolls as $nextPayroll){
            _debug([
                'Project payroll' => [
                    'Year' => $nextPayroll['year'],
                    'Month' => $nextPayroll['month'],
                    'Frequency sequence' => $nextPayroll['frequency_sequence'],
                ]
            ]);
        }

        $nextPayrolls = $currentUpToEndOfYear['next'];
        $employeeShift = $context->employee->shifts->first();

        $employeeDatePeriodUnpaidLeaves = app(LeaveRepository::class)
            ->model()::join('leave_types', 'leave_types.id', '=', 'leaves.leave_type_id')
            ->where('leave_types.is_paid', 0)
            ->where('employee_id', $context->employee->id)
            ->whereBetween('date', [$context->payroll->end_date->copy()->addDay()->toDateString(), $context->payroll->year . '-12-31']);

        $employeeDatePeriodUnpaidLeaves = Fractal::collection(
            $employeeDatePeriodUnpaidLeaves->get(),
            LeaveBasicTransformer::class
        )['data'];

        foreach($nextPayrolls as $nextPayroll){

            if(!isset($nextPayrollBasicPayAssumptions[$nextPayroll->month])){
                $nextPayrollBasicPayAssumptions[$nextPayroll->month] = BigDecimal::zero();
            }

            $datePeriod = CarbonPeriod::create($nextPayroll->start, $nextPayroll->end);
            $workDates = [];

            foreach($datePeriod as $date){

                if(empty($employeeShift))continue;

                //Skip if employee shift is not assigned to a date range
                if($employeeShift->pivot->stated_shift_end_date){
                    if(!$date->between($employeeShift->pivot->start_date, $employeeShift->pivot->end_date)){
                        continue;
                    }
                }

                if(!$employeeShift->pivot->stated_shift_end_date && $date->lt($employeeShift->pivot->start_date))continue;

                $unpaidLeaves = collect($employeeDatePeriodUnpaidLeaves)->where('date', $date->toDateString());
                $hasUnpaidLeave = $unpaidLeaves->isNotEmpty();

                //Skip if employee has unpaid leave on this date
                if($hasUnpaidLeave)continue;

                $this->setShift($employeeShift);
                $this->setAttendanceSchedule($date);

                $dayOff = $this->attendanceScheduleIsDayOff;
                $holiday = $this->getCompanyHolidayByDate($date, $this->company->id);
                $holidayType = !empty($holiday) ? $holiday->type : null;

                $isDateIsHoliday = !empty($holidayType);
                $isLegalHoliday = in_array($holidayType, [HolidayType::LEGAL, HolidayType::DOUBLE]);
                $isSpecialHoliday = in_array($holidayType, [HolidayType::SPECIAL]);

                $shiftHolidayPolicyIsDayOff = $this->shiftHolidayPolicy == ShiftHolidayPolicy::DAY_OFF;

                if($dayOff) continue;

                if($shiftHolidayPolicyIsDayOff && $isSpecialHoliday) continue;

                $workDates[] = $date;
            }

            $this->frequencyWorkingDayCount = count($workDates);

            foreach($workDates as $date){

                $startingDateHolidayType = $this->getDateHolidayType($date->toDateString());
                $startingDateIsRestDay = in_array($date->dayOfWeek, $this->restDays);

                $this->setShift($employeeShift);
                $this->setAttendanceSchedule($date);

                $schedule = $this->attendanceSchedule;
                $schedule = $this->parseSchedule($schedule, $date);

                $workPeriods = $this->calculateWorkPeriods($schedule);
                list($scheduleBreakdown) = $this->breakdownWorkPeriods($workPeriods, $startingDateIsRestDay, $startingDateHolidayType);

                $totalWorkMinutes = collect($scheduleBreakdown)->filter(function($split){return $split['split_type'] == ShiftBreakDownSplitType::WORK;})->sum('split_duration');

                /**
                 * Instantiate Salary Statement Module Service
                 **/
                $salaryStatementModuleService = new SalaryStatementModuleServiceConcrete($context->payroll, $this->company);
                $companyPerDayAbleEarningsComponentSubTypeFilterSlugs = $salaryStatementModuleService->companyPerDayAbleEarningsComponentSubTypeFilterSlugs([CompensationEnum::BASIC_PAY]);
                $companyPerDayAbleEarningsComponentSubTypeFilterSlug = $companyPerDayAbleEarningsComponentSubTypeFilterSlugs[0];
                $employeePayrollComponentFilters = (object)[
                    'employee_ids' => [$context->employee->id],
                    'payroll_componentable_type' => [Relation::getMorphAlias(Compensation::class)],
                    'payroll_componentable_component_sub_types' => [$companyPerDayAbleEarningsComponentSubTypeFilterSlug],
                    'payroll_componentable_date' => $date->toDateString()
                ];
                $employeePerDayableCompensations = app(EmployeePayrollComponentRepository::class)->list($employeePayrollComponentFilters);

                /**
                 * Employee assigned compensations (by attendance if amountable)
                 **/
                $employeePerDayableCompensations = $employeePerDayableCompensations->filter(function ($compensation){
                    $payrollComponentIsAmountable = in_array($compensation->payrollComponentable->type, [
                        CompensationEnum::BASIC_PAY
                    ]);

                    $payTypeIsByAttendance = $compensation->pay_type == PayType::BY_ATTENDANCE;

                    return !$payrollComponentIsAmountable || $payTypeIsByAttendance;
                });

                /**
                 * Limit to basic pay since we are assuming payable complete attendance
                 **/
                $employeePerDayableCompensations = $employeePerDayableCompensations->filter(function ($compensation){
                    return ($compensation->payrollComponentable->type == CompensationEnum::BASIC_PAY);
                });

                $employeePerDayableCompensationsPayload = $employeePerDayableCompensations
                    ->mapWithKeys(fn ($compensation) => [
                        $compensation->payrollComponentable->component_sub_type->value => [
                            'hourly_rate' => BigDecimal::zero(),
                        ],
                    ])
                    ->all();

                /**
                 * Get basic pay hourly rate
                 **/
                foreach($employeePerDayableCompensations as $employeePerDayableCompensation){

                    if($this->frequencyWorkingDayCount < 1)break;

                    if($totalWorkMinutes < 1)break;

                    $payrollComponentIsAmountable = $employeePerDayableCompensation->payrollComponentable->type == CompensationEnum::BASIC_PAY;

                    if($payrollComponentIsAmountable){

                        $componentableSubType = $employeePerDayableCompensation->payrollComponentable->component_sub_type->value;

                        if ($employeePerDayableCompensation->payrollComponentable->type == CompensationEnum::BASIC_PAY) {
                            if (isset($employeePerDayableCompensationsPayload[$componentableSubType])) {
                                $employeePerDayableCompensationsPayload[$componentableSubType]['hourly_rate'] =
                                    $employeePerDayableCompensationsPayload[$componentableSubType]['hourly_rate']->plus($this->getAssignedPayrollComponentHourlyRate(
                                        $payrollPayFrequency,
                                        $employeePerDayableCompensation,
                                        $totalWorkMinutes
                                    ));
                            }
                        }
                    }
                }

                $holiday = $this->getCompanyHolidayByDate($date, $this->company->id);
                $holidayType = !empty($holiday) ? $holiday->type : null;
                $isDoubleHoliday = $holidayType == HolidayType::DOUBLE;

                $baseMultiplier = $isDoubleHoliday ? BigDecimal::of('2') : BigDecimal::of('1');
                $hours = BigDecimal::of((string)$totalWorkMinutes)->dividedBy(BigInteger::of('60'), 6, RoundingMode::HalfUp);

                $dayBasicPay = $hours
                    ->multipliedBy($employeePerDayableCompensationsPayload[$companyPerDayAbleEarningsComponentSubTypeFilterSlug]['hourly_rate'])
                    ->multipliedBy($baseMultiplier);

                $nextPayrollBasicPayAssumptions[$nextPayroll->month] = $nextPayrollBasicPayAssumptions[$nextPayroll->month]->plus($dayBasicPay);

                if($debugEnabled){
                    _debug([
                        'Date' => $date->toDateString(),
                        'Slug' => $companyPerDayAbleEarningsComponentSubTypeFilterSlug,
                        'Total work day minutes' => $totalWorkMinutes,
                        'Earnings payload' => array_map(function($payload){
                            return [
                                'hourly_rate' => (string)$payload['hourly_rate'],
                            ];
                        }, $employeePerDayableCompensationsPayload),
                        'Earnings' => $dayBasicPay->toString()
                    ]);
                }
            }
        }

        return $nextPayrollBasicPayAssumptions;
    }
}
