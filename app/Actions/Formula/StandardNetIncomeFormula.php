<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class StandardNetIncomeFormula
{
    public string $slug = 'standard-net-income';

    public function handle(SalaryStatementContext $context, $next)
    {
        $debugEnabled = false;

        $pipelinePayload = $context->pipelinePayload->where('formula_slug', $this->slug)->first();
        $formula = $pipelinePayload['formula'];

        $runningTaxable = BigDecimal::of($context->runningValues['taxable'] ?? '0');
        $totalNonTaxable = BigDecimal::of($context->totals['nontaxable'] ?? '0');

        $totalDeduction = BigDecimal::zero();
        $totalWithholdingTax = BigDecimal::of($context->totals['withholding_tax'] ?? '0');

        $totalNet = BigDecimal::zero();

        foreach ($context->statementDetails as $detail) {

            $totalDeduction = $totalDeduction->plus(BigDecimal::of((string)$detail['deduction']));
        }

        $totalNet = $totalNet
            ->plus($runningTaxable)
            ->plus($totalNonTaxable)
            ->minus($totalWithholdingTax)
            ->minus($totalDeduction);

        $context->totals = [
            ...$context->totals,
            'deduction' => (string)$totalDeduction->toScale(6, RoundingMode::HalfUp),
            'net' => (string)$totalNet->toScale(6, RoundingMode::HalfUp)
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
            'nontaxable' => 0.0,
            'deduction' => 0.0,
            'contribution' => 0.0,
            'withholding_tax' => 0.0,
            'net' => (string)$totalNet->toScale(6, RoundingMode::HalfUp)
        ];

        $context->statementDetails[] = $statementDetail;

        return $next($context);
    }
}
