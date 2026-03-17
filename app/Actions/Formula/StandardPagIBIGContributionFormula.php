<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;
use App\Enums\Formulable;
use App\Enums\FormulableComponentSubType;
use App\Enums\SalaryStatementDetailComponentValueType;
use App\Facades\Fractal;
use App\Transformers\SalaryStatementDetail\PipelineChainableTransformer;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class StandardPagIBIGContributionFormula
{
    public string $slug = 'standard-pag-ibig-contribution';

    public function handle(SalaryStatementContext $context, $next)
    {
        $debugEnabled = false;
        $pipelinePayload = $context->pipelinePayload->where('formula_slug', $this->slug)->first();
        $formulableModel = $pipelinePayload['formulable_model'];
        $companyFormula = $formulableModel->companyFormula;
        $formulaSettings = $companyFormula->settings;
        $formula = $pipelinePayload['formula'];

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
                'Formula settings' => $formulaSettings->cast,
                'Statement details' => $context->statementDetails
            ]);
        }

        $totalTaxable = BigDecimal::of($context->totals['taxable']);

        if($context->additionalSalaryStatements->isNotEmpty()){

            $period['period_start'] = $context->additionalSalaryStatements->first()->payroll_start_date;

            foreach($context->additionalSalaryStatements as $salaryStatement){

                $statementDetails = Fractal::collection(
                    $salaryStatement->details->where('formulable_type', Formulable::EARNINGS->value),
                    PipelineChainableTransformer::class
                )['data'];

                foreach ($statementDetails as $detail) {

                    $totalTaxable = $totalTaxable->plus(BigDecimal::of((string)$detail['taxable']));
                }
            }
        }

        $trigger = $context->flags['is_monthly'] ||
            $context->flags['is_semimonthly_and_is_2nd_half'] ||
            $context->flags['is_weekly_and_is_last_split_of_month'];

        if($totalTaxable->isGreaterThan(BigDecimal::zero()) && ($trigger || $isFinalPayState)) {

            $statementDetail = [
                'id' => null,
                'statement_level' => true,
                'formulable_type' => $formula->formulable_type->value,
                'component_type' => $formula->component_type->value,
                'component_sub_type' => FormulableComponentSubType::PH_PAG_IBIG->value,
                'component_name' => FormulableComponentSubType::PH_PAG_IBIG->label(),
                'component_values' => null,
                'taxable' => 0.0,
                'nontaxable' => 0.0,
                'deduction' => 0.0,
                'contribution' => 0.0,
                'withholding_tax' => 0.0,
                'net' => 0.0,
            ];

            $contribution = $this->getContribution($formulaSettings->cast, $totalTaxable->toString());
            $componentValues = [
                ...$contribution,
                'type' => SalaryStatementDetailComponentValueType::PH_PAG_IBIG->value,
                ...$period
            ];

            $statementDetail['component_values'] = $componentValues;
            $statementDetail['contribution'] = $contribution['employee_share']['regular'];

            $context->statementDetails[] = $statementDetail;
        }

        return $next($context);
    }

    public function getContribution($castedCompanyFormulaSettings, $compensation): array
    {
        $settings = collect($castedCompanyFormulaSettings);

        /**
         * Rates
         **/
        $rates = $settings->where('key', 'rates')->first()->value;
        $compensationMinimum = collect($rates)->where('key', 'compensation_minimum')->first();
        $employeeShareOnMinimumAndBelow = collect($rates)->where('key', 'employee_share_on_minimum_and_below')->first();
        $employeeShareOnAboveMinimum = collect($rates)->where('key', 'employee_share_on_above_minimum')->first();
        $employerShare = collect($rates)->where('key', 'employer_share')->first();

        /**
         * Ceiling range
         **/
        $ceilingRange = $settings->where('key', 'ceiling_range')->first()->value;
        $maximumFundSalary = collect($ceilingRange)->where('key', 'maximum_fund_salary')->first();

        $compensationMinimumValue = BigDecimal::of($compensationMinimum->value);
        $employeeShareOnMinimumAndBelowValue = BigDecimal::of($employeeShareOnMinimumAndBelow->value);
        $employeeShareOnAboveMinimumValue = BigDecimal::of($employeeShareOnAboveMinimum->value);
        $maximumFundSalaryValue = BigDecimal::of($maximumFundSalary->value);

        $premiumBaseCredit = BigDecimal::zero();
        $employeeShareValue = BigDecimal::zero();
        $employerShareValue = BigDecimal::of($employerShare->value);

        $compensation = BigDecimal::of((string)$compensation);

        $result = [
            'employee_share' => [
                'regular' => '0.000000',
                'total' => '0.000000',
            ],
            'employer_share' => [
                'regular' => '0.000000',
                'total' => '0.000000',
            ],
            'total' => '0.000000'
        ];

        if($compensation->isLessThanOrEqualTo($compensationMinimumValue)){

            $employeeShareValue = $employeeShareOnMinimumAndBelowValue;
            $premiumBaseCredit = $compensation;

        } else if($compensation->isGreaterThan($compensationMinimumValue) && $compensation->isLessThan($maximumFundSalaryValue)){

            $employeeShareValue = $employeeShareOnAboveMinimumValue;
            $premiumBaseCredit = $compensation;

        } else {

            $employeeShareValue = $employeeShareOnAboveMinimumValue;
            $premiumBaseCredit = $maximumFundSalaryValue;
        }

        $contributionPercentage = $employeeShareValue->plus($employerShareValue);

        $result['total'] = (string)$premiumBaseCredit->multipliedBy($contributionPercentage)->toScale(4, RoundingMode::HalfUp);

        $employeeShareRegular = (string)$premiumBaseCredit->multipliedBy($employeeShareValue)->toScale(4, RoundingMode::HalfUp);
        $result['employee_share']['regular'] = $employeeShareRegular;
        $result['employee_share']['total'] = $employeeShareRegular;

        $employerShareRegular = (string)$premiumBaseCredit->multipliedBy($employerShareValue)->toScale(4, RoundingMode::HalfUp);
        $result['employer_share']['regular'] = $employerShareRegular;
        $result['employer_share']['total'] = $employerShareRegular;

        return $result;
    }
}
