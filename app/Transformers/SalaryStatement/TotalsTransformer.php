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
        $totalBasicGross = BigDecimal::of($salaryStatementTotals->total_basic_gross);
        $totalTaxable = BigDecimal::of($salaryStatementTotals->total_taxable);
        $totalWithholdingTax = BigDecimal::of($salaryStatementTotals->total_withholding_tax);
        $totalNet = BigDecimal::of($salaryStatementTotals->total_net);

        return [
            'basic_gross' => $totalBasicGross->toScale(4, RoundingMode::HalfUp),
            'taxable' => $totalTaxable->toScale(4, RoundingMode::HalfUp),
            'withholding_tax' => $totalWithholdingTax->toScale(4, RoundingMode::HalfUp),
            'net' => $totalNet->toScale(4, RoundingMode::HalfUp),
        ];
    }
}
