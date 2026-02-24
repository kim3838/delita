<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;
use App\Enums\Formulable;
use App\Enums\PayFrequency;
use App\Enums\SalaryStatementDetailComponentValueType;
use App\Facades\Fractal;
use App\Transformers\SalaryStatementDetail\PipelineChainableTransformer;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class StandardWithHoldingTaxCompensationFormula
{
    public string $slug = 'standard-withholding-tax-compensation';

    public function handle(SalaryStatementContext $context, $next)
    {
        $debugEnabled = false;
        $pipelinePayload = $context->pipelinePayload->where('formula_slug', $this->slug)->first();
        $formulableModel = $pipelinePayload['formulable_model'];
        $companyFormula = $formulableModel->companyFormula;
        $formulaSettings = $companyFormula->settings;
        $formula = $pipelinePayload['formula'];

        $coverage = [
            'coverage_start' => $context->payroll->start_date?->toDateString(),
            'coverage_end' => $context->payroll->end_date?->toDateString(),
        ];

        if($debugEnabled){
            _debug([
                'Formula slug' => $this->slug,
                'Formulable' => get_class($formulableModel),
                'Company formula' => get_class($companyFormula),
                'Formula' => get_class($formula),
                'Totals' => $context->totals,
                'Running values' => $context->runningValues
            ]);
        }

        $runningTaxable = BigDecimal::of($context->runningValues['taxable'] ?? '0');

        if($context->additionalSalaryStatements->isNotEmpty()){

            $coverage['coverage_start'] = $context->additionalSalaryStatements->first()->payroll_start_date;

            foreach($context->additionalSalaryStatements as $salaryStatement){

                $statementDetails = Fractal::collection(
                    $salaryStatement->details->where('formulable_type', Formulable::EARNINGS->value),
                    PipelineChainableTransformer::class
                )['data'];

                foreach ($statementDetails as $detail) {

                    $runningTaxable = $runningTaxable->plus(BigDecimal::of((string)$detail['taxable']));
                }
            }
        }

        if(
            $context->flags['is_monthly'] ||
            $context->flags['is_semimonthly_and_is_2nd_half'] ||
            $context->flags['is_weekly_and_is_last_split_of_month']
        ){
            $withholdingTax = $this->getIntended($formulaSettings->cast, $runningTaxable->toString(), PayFrequency::MONTHLY);

            $context->totals = [
                ...$context->totals,
                'withholding_tax' => $withholdingTax
            ];

            if($debugEnabled){
                _debug([
                    'Formula slug' => $this->slug,
                    'Totals' => $context->totals,
                    'Running values' => $context->runningValues
                ]);
            }

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
                'withholding_tax' => $withholdingTax,
                'net' => 0.0,
            ];

            $componentValues = [
                'type' => SalaryStatementDetailComponentValueType::PH_WITHHOLDING_TAX->value,
                'pay_frequency' => $context->payroll->pay_frequency?->label(),
                ...$coverage
            ];

            $statementDetail['component_values'] = $componentValues;

            $context->statementDetails[] = $statementDetail;
        }

        return $next($context);
    }

    public function getIntended($castedCompanyFormulaSettings, $taxable, PayFrequency $payFrequency): string
    {
        $settings = collect($castedCompanyFormulaSettings);
        $brackets = [];
        $frequencySlug = strtolower($payFrequency->label());

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

        return (string)$withholdingTax->toScale(6, RoundingMode::HalfUp);
    }
}
