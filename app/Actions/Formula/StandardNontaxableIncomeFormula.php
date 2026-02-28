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

        //Example non taxable income
        //$totalNonTaxable = $totalNonTaxable->plus(BigDecimal::of('25000'));

        foreach ($context->statementDetails as $detail) {

            $totalNonTaxable = $totalNonTaxable->plus(BigDecimal::of((string)$detail['nontaxable']));
        }

        if($totalNonTaxable->isGreaterThan(BigDecimal::zero())){

            $context->totals = [
                ...$context->totals,
                'nontaxable' => $totalNonTaxable->toScale(6, RoundingMode::HalfUp)->toString()
            ];

            if($debugEnabled){
                _debug([
                    'Formula slug' => $this->slug,
                    'Totals' => $context->totals,
                ]);
            }

            $statementDetail = [
                'id' => null,
                'formulable_type' => $formula->formulable_type->value,
                'component_type' => null,
                'component_name' => null,
                'component_values' => null,
                'taxable' => 0.0,
                'nontaxable' => $totalNonTaxable->toScale(6, RoundingMode::HalfUp)->toString(),
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
