<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class StandardTaxableIncomeFormula
{
    public string $slug = 'standard-taxable-income';

    public function handle(SalaryStatementContext $context, $next)
    {
        $debugEnabled = false;

        $pipelinePayload = $context->pipelinePayload->where('formula_slug', $this->slug)->first();
        $formula = $pipelinePayload['formula'];

        $totalTaxable = BigDecimal::of($context->totals['taxable'] ?? '0');
        $totalContribution = BigDecimal::zero();

        foreach ($context->statementDetails as $detail) {

            $totalContribution = $totalContribution->plus(BigDecimal::of((string)$detail['contribution']));
        }

        $totalTaxable = $totalTaxable->minus($totalContribution);

        $context->totals = [
            ...$context->totals,
            'taxable' => (string)$totalTaxable->toScale(6, RoundingMode::HalfUp),
            'contribution' => (string)$totalContribution->toScale(6, RoundingMode::HalfUp),
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
            'taxable' => $context->totals['taxable'],
            'nontaxable' => 0.0,
            'deduction' => 0.0,
            'contribution' => 0.0,
            'withholding_tax' => 0.0,
            'net' => 0.0,
        ];

        $context->statementDetails[] = $statementDetail;

        return $next($context);
    }
}
