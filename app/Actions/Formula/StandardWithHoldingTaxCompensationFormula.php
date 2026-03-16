<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;
use App\Enums\Compensation as CompensationEnum;
use App\Enums\Deduction as DeductionEnum;
use App\Enums\Formulable;
use App\Enums\FormulableComponentSubType;
use App\Enums\PayFrequency;
use App\Enums\SalaryStatementDetailComponentValueType;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Carbon\Carbon;

class StandardWithHoldingTaxCompensationFormula
{
    public string $slug = 'standard-withholding-tax-compensation';

    public int $formulaSettingsPayrollCalendarMonth = 0;
    public int $formulaSettingsPayrollCalendarMonthDay = 0;

    public function handle(SalaryStatementContext $context, $next)
    {
        $debugEnabled = false;
        $pipelinePayload = $context->pipelinePayload->where('formula_slug', $this->slug)->first();
        $formulableModel = $pipelinePayload['formulable_model'];
        $companyFormula = $formulableModel->companyFormula;
        $formulaSettings = $companyFormula->settings;
        $formula = $pipelinePayload['formula'];

        $this->formulaSettings($formulaSettings->cast);

        $isFinalPayState = $context->isFinalPayState;

        $period = [
            'period_start' => $context->payroll->start_date?->toDateString(),
            'period_end' => $context->payroll->end_date?->toDateString(),
        ];

        if($debugEnabled){
            _debug([
                'Formula slug' => $this->slug,
                'Is final pay state' => $isFinalPayState,
                'Formulable' => get_class($formulableModel),
                'Company formula' => get_class($companyFormula),
                'Formula' => get_class($formula),
                'Totals' => $context->totals,
            ]);
        }

        $totalNonTaxable = BigDecimal::of($context->totals['nontaxable'] ?? '0');

        $totalTaxable = BigDecimal::of($context->totals['taxable'] ?? '0');
        $totalTaxableBonus = BigDecimal::of($context->totals['taxable_bonus'] ?? '0');

        $totalContribution = BigDecimal::zero();

        foreach ($context->statementDetails as $detail) {

            $totalContribution = $totalContribution->plus(BigDecimal::of((string)$detail['contribution']));
        }

        $totalTaxable = $totalTaxable->plus($totalTaxableBonus)->minus($totalContribution);

        $trigger = $context->flags['is_monthly'] ||
            $context->flags['is_semimonthly_and_is_1st_half'] ||
            $context->flags['is_semimonthly_and_is_2nd_half'] ||
            $context->flags['is_weekly_and_is_last_split_of_month'];

        if($trigger || $isFinalPayState) {

            $withHoldingTaxFrequencyBase = PayFrequency::MONTHLY;

            switch($context->payroll->pay_frequency){
                case PayFrequency::MONTHLY:
                case PayFrequency::WEEKLY: $withHoldingTaxFrequencyBase = PayFrequency::MONTHLY;
                    break;
                case PayFrequency::SEMIMONTHLY: $withHoldingTaxFrequencyBase = PayFrequency::SEMIMONTHLY;
            }

            /**
             * Withholding tax for payroll's period
             **/
            $withholdingTax = $this->getIntended($formulaSettings->cast, $totalTaxable->toString(), $withHoldingTaxFrequencyBase);

            $context->totals = [
                ...$context->totals,
                'withholding_tax' => $withholdingTax
            ];

            if($debugEnabled){
                _debug([
                    'Formula slug' => $this->slug,
                    'Totals' => $context->totals,
                    'Total taxable' => $totalTaxable->toScale(6, RoundingMode::HalfUp)->toString(),
                    'Withholding tax' => $withholdingTax,
                ]);
            }

            $statementDetail = [
                'id' => null,
                'statement_level' => true,
                'formulable_type' => $formula->formulable_type->value,
                'component_type' => $formula->component_type->value,
                'component_sub_type' => FormulableComponentSubType::PH_WITHHOLDING_TAX_COMPENSATION->value,
                'component_name' => FormulableComponentSubType::PH_WITHHOLDING_TAX_COMPENSATION->label(),
                'component_values' => null,
                'taxable' => 0.0,
                'nontaxable' => 0.0,
                'deduction' => 0.0,
                'contribution' => 0.0,
                'withholding_tax' => $withholdingTax,
                'net' => 0.0,
            ];

            $componentValues = [
                'type' => SalaryStatementDetailComponentValueType::PH_WITHHOLDING_TAX->value,
                ...$period
            ];

            $statementDetail['component_values'] = $componentValues;

            $context->statementDetails[] = $statementDetail;

            /**
             * Tax annualization
             *
             * If payroll month is annual cutoff month and day is belong to payroll period,
             * annualize tax
             **/
            $annualCutoff = Carbon::parse(implode('-', [
                $context->payroll->year,
                str_pad($this->formulaSettingsPayrollCalendarMonth, 2, '0', STR_PAD_LEFT),
                str_pad($this->formulaSettingsPayrollCalendarMonthDay, 2, '0', STR_PAD_LEFT)
            ]));
            $taxAnnualize = $annualCutoff->between($context->payroll->start_date, $context->payroll->end_date);

            if($taxAnnualize || $isFinalPayState){

                $payrollYearSalaryStatements = $context->getPayrollYearSalaryStatements($context->payroll, $context->employee, $context->salaryStatement, $context->flags['rebuild_statement_level']);
                $payrollYearTotalTaxable = $context->getTotalFromSalaryStatementCollection($payrollYearSalaryStatements, 'taxable');
                $payrollYearTotalTaxWithheld = $context->getTotalFromSalaryStatementCollection($payrollYearSalaryStatements, 'withholding_tax');

                if ($debugEnabled) {
                    _debug([
                        'Payroll year (w/o period) total taxable' => $payrollYearTotalTaxable->toString(),
                        'Payroll year (w/o period) total tax withheld' => $payrollYearTotalTaxWithheld->toString(),
                    ]);
                }

                $payrollPeriodTotalTaxable = $totalTaxable;
                $payrollPeriodWithholdingTax = BigDecimal::of($withholdingTax);

                $payrollYearTotalTaxable = $payrollYearTotalTaxable->plus($payrollPeriodTotalTaxable);
                $payrollYearTotalTaxWithheld = $payrollYearTotalTaxWithheld->plus($payrollPeriodWithholdingTax);

                $annualWithholdingTax = $this->getIntended($formulaSettings->cast, $payrollYearTotalTaxable->toString(), null, 'annual');
                $annualWithholdingTax = BigDecimal::of($annualWithholdingTax);

                $adjustment = $payrollYearTotalTaxWithheld->minus($annualWithholdingTax);

                if ($debugEnabled) {
                    _debug([
                        'Rebuild statement level' => $context->flags['rebuild_statement_level'],
                        'Annual cutoff month' => $this->formulaSettingsPayrollCalendarMonth,
                        'Annual cutoff day' => $this->formulaSettingsPayrollCalendarMonthDay,
                        'Annual cutoff' => $annualCutoff->toDateString(),
                        'Payroll start date' => $context->payroll->start_date->toDateString(),
                        'Payroll end date' => $context->payroll->end_date->toDateString(),
                        'Tax annualize' => $taxAnnualize,
                        'Payroll period total taxable' => $payrollPeriodTotalTaxable->toString(),
                        'Payroll period withholding tax' => $payrollPeriodWithholdingTax->toString(),
                        'Payroll year total taxable' => $payrollYearTotalTaxable->toString(),
                        'Payroll year total tax withheld' => $payrollYearTotalTaxWithheld->toString(),
                        'Annual withholding tax' => $annualWithholdingTax->toString(),
                    ]);
                }

                /**
                 * If adjustment is negative, this means that the employee's annual tax is underpaid and needed negative adjustment
                 **/
                if($adjustment->toScale(2, RoundingMode::HalfUp)->isLessThan(BigDecimal::zero())){

                    $negativeAdjustment = $adjustment->abs();

                    $negativeAdjustmentComponentValues = [
                        'type' => SalaryStatementDetailComponentValueType::PH_WITHHOLDING_TAX_DEFICIT->value,
                        'withholding_tax_total_annual_taxable' => $payrollYearTotalTaxable->toScale(2, RoundingMode::HalfUp)->toString(),
                        'withholding_tax_withheld' => $payrollYearTotalTaxWithheld->toScale(2, RoundingMode::HalfUp)->toString(),
                        'withholding_tax_actual_annual_tax' => $annualWithholdingTax->toScale(2, RoundingMode::HalfUp)->toString(),
                        'withholding_tax_adjustment' => $negativeAdjustment->toScale(2, RoundingMode::HalfUp)->toString(),
                    ];

                    if ($debugEnabled) {
                        _debug([
                            'Withholding tax negative adjustment' => $negativeAdjustment->toScale(2, RoundingMode::HalfUp)->toString(),
                        ]);
                    }

                    $context->statementDetails[] = [
                        'id' => null,
                        'statement_level' => true,
                        'formulable_type' => Formulable::DEDUCTIONS->value,
                        'component_type' => DeductionEnum::TAX_ADJUSTMENT->value,
                        'component_sub_type' => FormulableComponentSubType::TAX_DEFICIT->value,
                        'component_name' => FormulableComponentSubType::TAX_DEFICIT->label(),
                        'component_values' => $negativeAdjustmentComponentValues,
                        'taxable' => 0.0,
                        'nontaxable' => 0.0,
                        'deduction' => $negativeAdjustment->toScale(6, RoundingMode::HalfUp)->toString(),
                        'contribution' => 0.0,
                        'withholding_tax' => 0.0,
                        'net' => 0.0,
                    ];
                }

                /**
                 * If adjustment is positive, this means that the employee's annual tax is overpaid and needed positive adjustment
                 **/
                if($adjustment->toScale(2, RoundingMode::HalfUp)->isGreaterThan(BigDecimal::zero())){
                    $positiveAdjustmentComponentValues = [
                        'type' => SalaryStatementDetailComponentValueType::PH_WITHHOLDING_TAX_REFUND->value,
                        'withholding_tax_total_annual_taxable' => $payrollYearTotalTaxable->toScale(2, RoundingMode::HalfUp)->toString(),
                        'withholding_tax_withheld' => $payrollYearTotalTaxWithheld->toScale(2, RoundingMode::HalfUp)->toString(),
                        'withholding_tax_actual_annual_tax' => $annualWithholdingTax->toScale(2, RoundingMode::HalfUp)->toString(),
                        'withholding_tax_adjustment' => $adjustment->toScale(2, RoundingMode::HalfUp)->toString(),
                    ];

                    if ($debugEnabled) {
                        _debug([
                            'Withholding tax positive adjustment' => $adjustment->toScale(2, RoundingMode::HalfUp)->toString(),
                        ]);
                    }

                    $totalNonTaxable = $totalNonTaxable->plus($adjustment);

                    $context->totals = [
                        ...$context->totals,
                        'nontaxable' => $totalNonTaxable->toScale(6, RoundingMode::HalfUp)->toString()
                    ];

                    $context->statementDetails[] = [
                        'id' => null,
                        'statement_level' => true,
                        'formulable_type' => Formulable::EARNINGS->value,
                        'component_type' => CompensationEnum::TAX_ADJUSTMENT->value,
                        'component_sub_type' => FormulableComponentSubType::TAX_REFUND->value,
                        'component_name' => FormulableComponentSubType::TAX_REFUND->label(),
                        'component_values' => $positiveAdjustmentComponentValues,
                        'taxable' => 0.0,
                        'nontaxable' => $adjustment->toScale(6, RoundingMode::HalfUp)->toString(),
                        'deduction' => 0.0,
                        'contribution' => 0.0,
                        'withholding_tax' => 0.0,
                        'net' => 0.0,
                    ];
                }
            }
        }

        return $next($context);
    }

    public function formulaSettings($castedCompanyFormulaSettings): void
    {
        $settings = collect($castedCompanyFormulaSettings);
        $annualFrequencyCutoff = $settings->where('key', 'annual_frequency_cut_off')->first()->value;
        $annualFrequencyCutoff = collect($annualFrequencyCutoff);
        $annualCutoffMonth = $annualFrequencyCutoff->where('key', 'month')->first()->value;
        $annualCutoffDay = $annualFrequencyCutoff->where('key', 'day')->first()->value;

        $this->formulaSettingsPayrollCalendarMonth = $annualCutoffMonth;
        $this->formulaSettingsPayrollCalendarMonthDay = $annualCutoffDay;
    }

    public function getIntended($castedCompanyFormulaSettings, $taxable, ?PayFrequency $payFrequency, $fallbackSlug = 'monthly'): string
    {
        $settings = collect($castedCompanyFormulaSettings);
        $brackets = [];
        $frequencySlug = empty($payFrequency) ? $fallbackSlug : strtolower($payFrequency->label());

        $taxRates = $settings->where('key', $frequencySlug . '_tax_rates')->first()->value;

        foreach ($taxRates as $rate) {

            $bracketPayload = [];

            $bracket = $settings->where('key', $frequencySlug . "_bracket_" . $rate)->first()?->value;

            if(empty($bracket)) continue;

            $monthlyTaxable = collect($bracket)->where('key', 'taxable')->first()->value;

            $over = collect($monthlyTaxable)->where('key', 'over')->first()->value;
            $upTo = collect($monthlyTaxable)->where('key', 'up_to')->first()?->value;
            $taxRate = collect($bracket)->where('key', 'tax_rate')->first()->value;
            $baseTax = collect($bracket)->where('key', 'base_tax')->first()->value;

            $bracketPayload['over'] = BigDecimal::of($over);
            $bracketPayload['up_to'] = empty($upTo) ? null : BigDecimal::of($upTo);
            $bracketPayload['tax_rate'] = BigDecimal::of($taxRate);
            $bracketPayload['base_tax'] = BigDecimal::of($baseTax);

            $brackets[] = $bracketPayload;
        }

        $taxableValue = BigDecimal::of($taxable);
        $withholdingTax = BigDecimal::zero();

        foreach ($brackets as $bracket) {

            $over = $taxableValue->isGreaterThan(BigDecimal::of($bracket['over']));

            if($over && (empty($bracket['up_to']) || $taxableValue->isLessThanOrEqualTo(BigDecimal::of($bracket['up_to'])))){

                $taxMultiplier = $bracket['tax_rate'];
                $flatTax = $bracket['base_tax'];
                $excessOver = $taxableValue->minus($bracket['over']);

                $withholdingTax = $withholdingTax->plus($flatTax)->plus($excessOver->multipliedBy($taxMultiplier));

                break;
            }
        }

        return $withholdingTax->toScale(6, RoundingMode::HalfUp)->toString();
    }
}
