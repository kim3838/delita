<?php

namespace App\Transformers\Payroll;

use App\Models\Hydrations\PayrollTotals;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use League\Fractal\TransformerAbstract;

class TotalsTransformer extends TransformerAbstract
{
    public function transform(PayrollTotals $salaryStatementTotals): array
    {
        $totalEmployerContributionShare = BigDecimal::of($salaryStatementTotals->employer_contribution_share);
        $totalTaxable = BigDecimal::of($salaryStatementTotals->taxable);
        $totalWithholdingTax = BigDecimal::of($salaryStatementTotals->withholding_tax);
        $totalNet = BigDecimal::of($salaryStatementTotals->net);

        return [
            'employer_contribution_share' => $totalEmployerContributionShare->toScale(4, RoundingMode::HalfUp),
            'taxable' => $totalTaxable->toScale(4, RoundingMode::HalfUp),
            'withholding_tax' => $totalWithholdingTax->toScale(4, RoundingMode::HalfUp),
            'net' => $totalNet->toScale(4, RoundingMode::HalfUp),
        ];
    }
}
