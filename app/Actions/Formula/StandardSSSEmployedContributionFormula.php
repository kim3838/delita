<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;
use App\Enums\Formulable;
use App\Enums\SalaryStatementDetailComponentValueType;
use App\Facades\Fractal;
use App\Transformers\SalaryStatementDetail\PipelineChainableTransformer;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class StandardSSSEmployedContributionFormula
{
    public string $slug = 'standard-sss-employed-contribution';

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
                'type' => SalaryStatementDetailComponentValueType::PH_SSS->value,
                ...$period
            ];

            $statementDetail['component_values'] = $componentValues;
            $statementDetail['contribution'] = $contribution['employee_share']['total'];

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
        $employeeShare = collect($rates)->where('key', 'employee_share')->first();
        $employeeMpfEligibility = collect($rates)->where('key', 'employee_mpf_eligibility')->first();
        $employeeMpf = collect($rates)->where('key', 'employee_mpf')->first();

        $employerShare = collect($rates)->where('key', 'employer_share')->first();
        $employerMpf = collect($rates)->where('key', 'employer_mpf')->first();

        $employeeShareValue = BigDecimal::of($employeeShare->value);
        $employeeMpfEligibilityValue = BigDecimal::of($employeeMpfEligibility->value);
        $employeeMpfValue = BigDecimal::of($employeeMpf->value);

        $employerShareValue = BigDecimal::of($employerShare->value);
        $employerMpfValue = BigDecimal::of($employerMpf->value);

        /**
         * Employer EC
         **/
        $employerEc = $settings->where('key', 'employer_ec')->first()->value;
        $employerEcBeforeThreshold = collect($employerEc)->where('key', 'employer_ec_before_threshold')->first();
        $employerEcThreshold = collect($employerEc)->where('key', 'employer_ec_threshold')->first();
        $employerEcAfterThreshold = collect($employerEc)->where('key', 'employer_ec_after_threshold')->first();

        $employerEcBeforeThresholdValue = BigDecimal::of($employerEcBeforeThreshold->value);
        $employerEcThresholdValue = BigDecimal::of($employerEcThreshold->value);
        $employerEcAfterThresholdValue = BigDecimal::of($employerEcAfterThreshold->value);

        /**
         * Compensation range
         **/
        $compensationRange = $settings->where('key', 'compensation_range')->first()->value;
        $startingCompensationRange = collect($compensationRange)->where('key', 'starting_compensation_range')->first();
        $startingMsc = collect($compensationRange)->where('key', 'starting_msc')->first();
        $mscInterval = collect($compensationRange)->where('key', 'msc_interval')->first();
        $maxMsc = collect($compensationRange)->where('key', 'max_msc')->first();

        $startingCompensationRangeValue = BigDecimal::of($startingCompensationRange->value);
        $startingMscValue = BigDecimal::of($startingMsc->value);
        $mscIntervalValue = BigDecimal::of($mscInterval->value);
        $maxMscValue = BigDecimal::of($maxMsc->value);

        $result = [
            'employee_share' => [
                'regular' => '0.000000',
                'mpf' => '0.000000',
                'total' => '0.000000'
            ],
            'employer_share' => [
                'regular' => '0.000000',
                'mpf' => '0.000000',
                'ec' => '0.000000',
                'total' => '0.000000'
            ],
            'total' => '0.000000'
        ];

        $compensationMscBoundary = $startingCompensationRangeValue;
        $msc = $startingMscValue;
        $compensation = BigDecimal::of($compensation);

        while($compensation->isGreaterThan($compensationMscBoundary) && $msc->isLessThan($maxMscValue)){
            $msc = $msc->plus($mscIntervalValue);
            $compensationMscBoundary = $compensationMscBoundary->plus($mscIntervalValue);
        }

        $mscExcessOverMpfThreshold = $msc->minus($employeeMpfEligibilityValue);

        $employeeShareTemp = $msc->multipliedBy($employeeShareValue);
        $employeeMpf = ($msc->isGreaterThan($employeeMpfEligibilityValue)) ? ($mscExcessOverMpfThreshold->multipliedBy($employeeMpfValue)) : BigDecimal::zero();

        $result['employee_share']['regular'] = (string)$employeeShareTemp->minus($employeeMpf)->toScale(2, RoundingMode::HalfUp);
        $result['employee_share']['mpf'] = (string)$employeeMpf->toScale(2, RoundingMode::HalfUp);
        $result['employee_share']['total'] = (string)$employeeShareTemp->toScale(2, RoundingMode::HalfUp);

        $employerShareTemp = $msc->multipliedBy($employerShareValue);
        $employerMpf = ($msc->isGreaterThan($employeeMpfEligibilityValue)) ? ($mscExcessOverMpfThreshold->multipliedBy($employerMpfValue)) : BigDecimal::zero();
        $employerEc = $msc->isLessThan($employerEcThresholdValue) ? $employerEcBeforeThresholdValue : $employerEcAfterThresholdValue;

        $employerTotalShare = $employerShareTemp->plus($employerEc);

        $result['employer_share']['regular'] = (string)$employerShareTemp->minus($employerMpf)->toScale(2, RoundingMode::HalfUp);
        $result['employer_share']['mpf'] = (string)$employerMpf->toScale(2, RoundingMode::HalfUp);
        $result['employer_share']['ec'] = (string)$employerEc->toScale(2, RoundingMode::HalfUp);
        $result['employer_share']['total'] = (string)$employerTotalShare->toScale(2, RoundingMode::HalfUp);

        $result['total'] = (string)$employeeShareTemp->plus($employerTotalShare)->toScale(2, RoundingMode::HalfUp);

        return $result;
    }
}
