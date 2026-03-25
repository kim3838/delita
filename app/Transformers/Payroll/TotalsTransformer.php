<?php

namespace App\Transformers\Payroll;

use App\Facades\MoneyFormat;
use App\Models\Hydrations\PayrollTotals;
use Brick\Math\BigDecimal;
use League\Fractal\TransformerAbstract;

class TotalsTransformer extends TransformerAbstract
{
    public function transform(PayrollTotals $salaryStatementTotals): array
    {
        $totalEmployerContributionShare = BigDecimal::of($salaryStatementTotals->employer_contribution_share ?? 0);
        $totalTaxable = BigDecimal::of($salaryStatementTotals->taxable ?? 0);
        $totalWithholdingTax = BigDecimal::of($salaryStatementTotals->withholding_tax ?? 0);
        $totalTaxRefund = BigDecimal::of($salaryStatementTotals->tax_refund ?? 0);
        $totalNet = BigDecimal::of($salaryStatementTotals->net ?? 0);

        return [
            'employer_contribution_share' => MoneyFormat::toLocale($totalEmployerContributionShare, $salaryStatementTotals->company_currency_code),
            'taxable' => MoneyFormat::toLocale($totalTaxable, $salaryStatementTotals->company_currency_code),
            'withholding_tax' => MoneyFormat::toLocale($totalWithholdingTax, $salaryStatementTotals->company_currency_code),
            'tax_refund' => MoneyFormat::toLocale($totalTaxRefund, $salaryStatementTotals->company_currency_code),
            'net' => MoneyFormat::toLocale($totalNet, $salaryStatementTotals->company_currency_code),
        ];
    }
}
