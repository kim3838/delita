<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class InitializeSalaryStatementFormula
{
    public function handle(SalaryStatementContext $context, $next)
    {
        $shared = [];

        $totalTaxable = BigDecimal::zero();
        $totalNonTaxable = BigDecimal::zero();
        $totalDeduction = BigDecimal::zero();

        foreach ($context->statementDetails as $detail) {

            $totalTaxable = $totalTaxable->plus(BigDecimal::of((string)$detail['taxable']));
            $totalNonTaxable = $totalNonTaxable->plus(BigDecimal::of((string)$detail['nontaxable']));
            $totalDeduction = $totalDeduction->plus(BigDecimal::of((string)$detail['deduction']));
        }

        $shared['total_taxable'] = (string)$totalTaxable->toScale(6, RoundingMode::HalfUp);
        $shared['total_nontaxable'] = (string)$totalNonTaxable->toScale(6, RoundingMode::HalfUp);
        $shared['total_deduction'] = (string)$totalDeduction->toScale(6, RoundingMode::HalfUp);

        $context->shared = $shared;

        return $next($context);
    }
}
