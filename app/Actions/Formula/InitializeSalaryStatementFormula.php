<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class InitializeSalaryStatementFormula
{
    public function handle(SalaryStatementContext $context, $next)
    {
        $totals = [];
        $runningValues = [];

        $totalTaxable = BigDecimal::zero();
        $totalDeduction = BigDecimal::zero();

        foreach ($context->statementDetails as $detail) {

            $totalTaxable = $totalTaxable->plus(BigDecimal::of((string)$detail['taxable']));
            $totalDeduction = $totalDeduction->plus(BigDecimal::of((string)$detail['deduction']));
        }

        $totals['taxable'] = (string)$totalTaxable->toScale(6, RoundingMode::HalfUp);
        $totals['deduction'] = (string)$totalDeduction->toScale(6, RoundingMode::HalfUp);

        $runningValues['taxable'] = (string)$totalTaxable->toScale(6, RoundingMode::HalfUp);

        $context->totals = $totals;
        $context->runningValues = $runningValues;

        return $next($context);
    }
}
