<?php

namespace App\Transformers\SalaryStatement;

use App\Facades\MoneyFormat;
use App\Models\Hydrations\SalaryStatementTotals;
use League\Fractal\TransformerAbstract;

class TotalsTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementTotals $salaryStatementTotals): array
    {
        $totalBasicGross = MoneyFormat::toLocale($salaryStatementTotals->basic_gross ?? 0, $salaryStatementTotals->company_currency_code);
        $totalTaxable = MoneyFormat::toLocale($salaryStatementTotals->taxable ?? 0, $salaryStatementTotals->company_currency_code);
        $totalWithholdingTax = MoneyFormat::toLocale($salaryStatementTotals->withholding_tax ?? 0, $salaryStatementTotals->company_currency_code);
        $taxRefund = MoneyFormat::toLocale($salaryStatementTotals->tax_refund ?? 0, $salaryStatementTotals->company_currency_code);
        $totalNet = MoneyFormat::toLocale($salaryStatementTotals->net ?? 0, $salaryStatementTotals->company_currency_code);

        return [
            'basic_gross' => $totalBasicGross,
            'taxable' => $totalTaxable,
            'withholding_tax' => $totalWithholdingTax,
            'tax_refund' => $taxRefund,
            'net' => $totalNet,
        ];
    }
}
