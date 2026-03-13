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

class StandardPhilhealthContributionFormula
{
    public string $slug = 'standard-philhealth-contribution';

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

        if($totalTaxable->isGreaterThan(BigDecimal::zero()) && ($trigger || $isFinalPayState))
        {
            $statementDetail = [
                'id' => null,
                'statement_level' => true,
                'formulable_type' => $formula->formulable_type->value,
                'component_type' => $formula->component_type->value,
                'component_sub_type' => FormulableComponentSubType::PH_PHILHEALTH->value,
                'component_name' => FormulableComponentSubType::PH_PHILHEALTH->label(),
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
                'type' => SalaryStatementDetailComponentValueType::PH_PHILHEALTH->value,
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
        $rate = collect($rates)->where('key', 'rate')->first();
        $employeeShare = collect($rates)->where('key', 'employee_share')->first();
        $employerShare = collect($rates)->where('key', 'employer_share')->first();

        $rateValue = BigDecimal::of($rate->value);
        $employeeShareValue = BigDecimal::of($employeeShare->value);
        $employerShareValue = BigDecimal::of($employerShare->value);

        /**
         * Ceiling range
         **/
        $ceilingRange = $settings->where('key', 'ceiling_range')->first()->value;
        $startingCompensationMinimum = collect($ceilingRange)->where('key', 'starting_compensation_minimum')->first();
        $startingContribution = collect($ceilingRange)->where('key', 'starting_contribution')->first();
        $compensationCeiling = collect($ceilingRange)->where('key', 'compensation_ceiling')->first();
        $maxContribution = collect($ceilingRange)->where('key', 'max_contribution')->first();

        $startingCompensationMinimumValue = BigDecimal::of($startingCompensationMinimum->value);
        $startingContributionValue = BigDecimal::of($startingContribution->value);
        $compensationCeilingValue = BigDecimal::of($compensationCeiling->value);
        $maxContributionValue = BigDecimal::of($maxContribution->value);

        $compensation = BigDecimal::of((string)$compensation);
        $premium = BigDecimal::zero();

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

        if($compensation->isLessThanOrEqualTo($startingCompensationMinimumValue)){
            $premium = $startingContributionValue;
        } else if($compensation->isGreaterThanOrEqualTo($compensationCeilingValue)){
            $premium = $maxContributionValue;
        } else {
            $premium = $compensation->multipliedBy($rateValue);
        }

        $result['total'] = (string)$premium->toScale(2, RoundingMode::HalfUp);

        $employeeShareRegular = (string)$premium->multipliedBy($employeeShareValue)->toScale(2, RoundingMode::HalfUp);
        $result['employee_share']['regular'] = $employeeShareRegular;
        $result['employee_share']['total'] = $employeeShareRegular;

        $employerShareRegular = (string)$premium->multipliedBy($employerShareValue)->toScale(2, RoundingMode::HalfUp);
        $result['employer_share']['regular'] = $employerShareRegular;
        $result['employer_share']['total'] = $employerShareRegular;


        return $result;
    }
}
