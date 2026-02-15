<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;

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

        $employeeShareValue = (float)$employeeShare->value;
        $employeeMpfEligibilityValue = (float)$employeeMpfEligibility->value;
        $employeeMpfValue = (float)$employeeMpf->value;

        $employerShareValue = (float)$employerShare->value;
        $employerMpfValue = (float)$employerMpf->value;

        /**
         * Employer EC
         **/
        $employerEc = $settings->where('key', 'employer_ec')->first()->value;
        $employerEcBeforeThreshold = collect($employerEc)->where('key', 'employer_ec_before_threshold')->first();
        $employerEcThreshold = collect($employerEc)->where('key', 'employer_ec_threshold')->first();
        $employerEcAfterThreshold = collect($employerEc)->where('key', 'employer_ec_after_threshold')->first();

        $employerEcBeforeThresholdValue = (float)$employerEcBeforeThreshold->value;
        $employerEcThresholdValue = (float)$employerEcThreshold->value;
        $employerEcAfterThresholdValue = (float)$employerEcAfterThreshold->value;

        /**
         * Compensation range
         **/
        $compensationRange = $settings->where('key', 'compensation_range')->first()->value;
        $startingCompensationRange = collect($compensationRange)->where('key', 'starting_compensation_range')->first();
        $startingMsc = collect($compensationRange)->where('key', 'starting_msc')->first();
        $mscInterval = collect($compensationRange)->where('key', 'msc_interval')->first();
        $maxMsc = collect($compensationRange)->where('key', 'max_msc')->first();

        $startingCompensationRangeValue = (float)$startingCompensationRange->value;
        $startingMscValue = (float)$startingMsc->value;
        $mscIntervalValue = (float)$mscInterval->value;
        $maxMscValue = (float)$maxMsc->value;

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
        $monthlyCompensation = (float)$monthlyCompensation;

        while($monthlyCompensation > $compensationMscBoundary && $msc < $maxMscValue){
            $msc += $mscIntervalValue;
            $compensationMscBoundary = round($compensationMscBoundary + $mscIntervalValue, 2);
        }

        $mscExcessOverMpfThreshold = $msc - $employeeMpfEligibilityValue;

        $employeeShareTemp = $msc * $employeeShareValue;
        $employeeMpf = ($msc > $employeeMpfEligibilityValue) ? ($mscExcessOverMpfThreshold * $employeeMpfValue) : 0;

        $result['employee_share']['regular'] = $employeeShareTemp - $employeeMpf;
        $result['employee_share']['mpf'] = $employeeMpf;
        $result['employee_share']['total'] = $employeeShareTemp;

        $employerShareTemp = $msc * $employerShareValue;
        $employerMpf = ($msc > $employeeMpfEligibilityValue) ? ($mscExcessOverMpfThreshold * $employerMpfValue) : 0;
        $employerEc = $msc < $employerEcThresholdValue ? $employerEcBeforeThresholdValue : $employerEcAfterThresholdValue;

        $employerTotalShare = $employerShareTemp + $employerEc;

        $result['employer_share']['regular'] = $employerShareTemp - $employerMpf;
        $result['employer_share']['mpf'] = $employerMpf;
        $result['employer_share']['ec'] = $employerEc;
        $result['employer_share']['total'] = $employerTotalShare;

        $result['total'] = $employeeShareTemp + $employerTotalShare;

        return $result;
    }
}
