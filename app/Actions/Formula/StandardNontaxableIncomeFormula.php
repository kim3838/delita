<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class StandardNontaxableIncomeFormula
{
    public string $slug = 'standard-nontaxable-income';

    public function handle(SalaryStatementContext $context, $next)
    {
        $debugEnabled = false;

        $pipelinePayload = $context->pipelinePayload->where('formula_slug', $this->slug)->first();
        $formula = $pipelinePayload['formula'];

        $totalNonTaxable = BigDecimal::zero();

        foreach ($context->statementDetails as $detail) {

            $totalNonTaxable = $totalNonTaxable->plus(BigDecimal::of((string)$detail['nontaxable']));
        }

        if(!$totalNonTaxable->isZero()){

            $context->totals = [
                ...$context->totals,
                'nontaxable' => (string)$totalNonTaxable->toScale(6, RoundingMode::HalfUp)
            ];

            $context->runningValues = [
                ...$context->runningValues,
                'nontaxable' => (string)$totalNonTaxable->toScale(6, RoundingMode::HalfUp),
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
                'component_type' => null,
                'component_name' => null,
                'component_values' => null,
                'taxable' => 0.0,
                'nontaxable' => $context->runningValues['nontaxable'],
                'deduction' => 0.0,
                'contribution' => 0.0,
                'withholding_tax' => 0.0,
                'net' => 0.0,
            ];

            $context->statementDetails[] = $statementDetail;

        }

        return $next($context);
    }
}
