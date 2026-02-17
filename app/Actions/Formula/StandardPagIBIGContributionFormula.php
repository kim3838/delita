<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;
use App\Enums\PayFrequency;
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

        if($debugEnabled){
            _debug([
                'Formula slug' => $this->slug,
                'Formulable' => get_class($formulableModel),
                'Company formula' => get_class($companyFormula),
                'Formula' => get_class($formula),
                'Shared' => $context->shared,
                'Formula settings' => $formulaSettings->cast,
                'Statement details' => $context->statementDetails
            ]);
        }

        if($context->payroll->pay_frequency == PayFrequency::MONTHLY){

            $totalTaxable = $context->shared['total_taxable'];

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

            $contribution = $this->getContribution($formulaSettings->cast, $totalTaxable);
            $componentValues = [
                ...$contribution,
                'pay_frequency' => $context->payroll->pay_frequency?->label(),
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
            'employee_share' => '0.000000',
            'employer_share' => '0.000000',
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

        $result['total'] = (string)$premiumBaseCredit->multipliedBy($contributionPercentage)->toScale(6, RoundingMode::HalfUp);
        $result['employee_share'] = (string)$premiumBaseCredit->multipliedBy($employeeShareValue)->toScale(6, RoundingMode::HalfUp);
        $result['employer_share'] = (string)$premiumBaseCredit->multipliedBy($employerShareValue)->toScale(6, RoundingMode::HalfUp);

        return $result;
    }
}
