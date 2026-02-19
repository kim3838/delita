<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;
use App\Enums\PayFrequency;
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

        if($debugEnabled){
            _debug([
                'Formula slug' => $this->slug,
                'Formulable' => get_class($formulableModel),
                'Company formula' => get_class($companyFormula),
                'Formula' => get_class($formula),
                'Totals' => $context->totals,
                'Running values' => $context->runningValues,
                'Formula settings' => $formulaSettings->cast,
                'Statement details' => $context->statementDetails
            ]);
        }

        $totalTaxable = BigDecimal::of($context->totals['taxable']);

        if($totalTaxable->isGreaterThan(BigDecimal::zero()) && $context->payroll->pay_frequency == PayFrequency::MONTHLY){

            $statementDetail = [
                'id' => null,
                'formulable_type' => $formula->formulable_type->value,
                'component_type' => $formula->component_type->value,
                'component_name' => $formulableModel->name,
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
                'pay_frequency_label' => $context->payroll->pay_frequency?->label(),
                'coverage' => [
                    'start_date' => $context->payroll->start_date?->toDateString(),
                    'end_date' => $context->payroll->end_date?->toDateString(),
                ]
            ];

            $statementDetail['component_values'] = $componentValues;
            $statementDetail['contribution'] = $contribution['employee_share'];

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
            'employee_share' => '0.000000',
            'employer_share' => '0.000000',
            'total' => '0.000000'
        ];

        if($compensation->isLessThanOrEqualTo($startingCompensationMinimumValue)){
            $premium = $startingContributionValue;
        } else if($compensation->isGreaterThanOrEqualTo($compensationCeilingValue)){
            $premium = $maxContributionValue;
        } else {
            $premium = $compensation->multipliedBy($rateValue);
        }

        $result['total'] = (string)$premium->toScale(6, RoundingMode::HalfUp);
        $result['employee_share'] = (string)$premium->multipliedBy($employeeShareValue)->toScale(6, RoundingMode::HalfUp);
        $result['employer_share'] = (string)$premium->multipliedBy($employerShareValue)->toScale(6, RoundingMode::HalfUp);

        return $result;
    }
}
