<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class StandardSSSEmployedContributionFormula
{
    public string $slug = 'standard-sss-employed-contribution';

    public function handle(SalaryStatementContext $context, $next)
    {
        $pipelinePayload = $context->pipelinePayload->where('formula_slug', $this->slug)->first();
        $formulableModel = $pipelinePayload['formulable_model'];
        $companyFormula = $formulableModel->companyFormula;
        $formulaSettings = $companyFormula->settings;
        $formula = $pipelinePayload['formula'];

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

        $statementDetail['component_values'] = $contribution;
        $statementDetail['contribution'] = $contribution['employee_share']['total'];

        $context->statementDetails[] = $statementDetail;

        return $next($context);
    }

    public function getContribution($castedCompanyFormulaSettings, $monthlyCompensation): array
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
                'regular' => 0,
                'mpf' => 0,
                'total' => 0
            ],
            'employer_share' => [
                'regular' => 0,
                'mpf' => 0,
                'ec' => 0,
                'total' => 0
            ],
            'total' => 0
        ];

        $compensationMscBoundary = $startingCompensationRangeValue;
        $msc = $startingMscValue;
        $monthlyCompensation = BigDecimal::of($monthlyCompensation);

        while($monthlyCompensation->isGreaterThan($compensationMscBoundary) && $msc->isLessThan($maxMscValue)){
            $msc = $msc->plus($mscIntervalValue);
            $compensationMscBoundary = $compensationMscBoundary->plus($mscIntervalValue);
        }

        $mscExcessOverMpfThreshold = $msc->minus($employeeMpfEligibilityValue);

        $employeeShareTemp = $msc->multipliedBy($employeeShareValue);
        $employeeMpf = ($msc->isGreaterThan($employeeMpfEligibilityValue)) ? ($mscExcessOverMpfThreshold->multipliedBy($employeeMpfValue)) : BigDecimal::zero();

        $result['employee_share']['regular'] = (string)$employeeShareTemp->minus($employeeMpf)->toScale(6, RoundingMode::HalfUp);
        $result['employee_share']['mpf'] = (string)$employeeMpf->toScale(6, RoundingMode::HalfUp);
        $result['employee_share']['total'] = (string)$employeeShareTemp->toScale(6, RoundingMode::HalfUp);

        $employerShareTemp = $msc->multipliedBy($employerShareValue);
        $employerMpf = ($msc->isGreaterThan($employeeMpfEligibilityValue)) ? ($mscExcessOverMpfThreshold->multipliedBy($employerMpfValue)) : BigDecimal::zero();
        $employerEc = $msc->isLessThan($employerEcThresholdValue) ? $employerEcBeforeThresholdValue : $employerEcAfterThresholdValue;

        $employerTotalShare = $employerShareTemp->plus($employerEc);

        $result['employer_share']['regular'] = (string)$employerShareTemp->minus($employerMpf)->toScale(6, RoundingMode::HalfUp);
        $result['employer_share']['mpf'] = (string)$employerMpf->toScale(6, RoundingMode::HalfUp);
        $result['employer_share']['ec'] = (string)$employerEc->toScale(6, RoundingMode::HalfUp);
        $result['employer_share']['total'] = (string)$employerTotalShare->toScale(6, RoundingMode::HalfUp);

        $result['total'] = (string)$employeeShareTemp->plus($employerTotalShare)->toScale(6, RoundingMode::HalfUp);

        return $result;
    }
}
