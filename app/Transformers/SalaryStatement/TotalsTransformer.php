<?php

namespace App\Transformers\SalaryStatement;

use App\Models\Hydrations\SalaryStatementTotals;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use League\Fractal\TransformerAbstract;

class TotalsTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementTotals $salaryStatementTotals): array
    {
        $totalBasicGross = BigDecimal::of($salaryStatementTotals->basic_gross ?? 0);
        $totalTaxable = BigDecimal::of($salaryStatementTotals->taxable ?? 0);
        $totalWithholdingTax = BigDecimal::of($salaryStatementTotals->withholding_tax ?? 0);
        $totalNet = BigDecimal::of($salaryStatementTotals->net ?? 0);

        return [
            'basic_gross' => $totalBasicGross->toScale(4, RoundingMode::HalfUp),
            'taxable' => $totalTaxable->toScale(4, RoundingMode::HalfUp),
            'withholding_tax' => $totalWithholdingTax->toScale(4, RoundingMode::HalfUp),
            'net' => $totalNet->toScale(4, RoundingMode::HalfUp),
        ];
    }
}
