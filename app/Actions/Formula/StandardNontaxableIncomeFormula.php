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

        $totalNonTaxable = BigDecimal::of($context->shared['total_nontaxable'] ?? '0');

        foreach ($context->statementDetails as $detail) {

            $totalNonTaxable = $totalNonTaxable->plus(BigDecimal::of((string)$detail['nontaxable']));
        }

        if(!$totalNonTaxable->isZero()){

            $shared['total_nontaxable'] = (string)$totalNonTaxable->toScale(6, RoundingMode::HalfUp);

            $context->shared = $shared;

            if($debugEnabled){
                _debug([
                    'Formula slug' => $this->slug,
                    'Formula' => get_class($formula),
                    'Shared' => $context->shared,
                ]);
            }

            $statementDetail = [
                'id' => null,
                'formulable_type' => $formula->formulable_type->value,
                'component_type' => null,
                'component_name' => null,
                'component_values' => null,
                'taxable' => 0.0,
                'nontaxable' => $shared['total_nontaxable'],
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
