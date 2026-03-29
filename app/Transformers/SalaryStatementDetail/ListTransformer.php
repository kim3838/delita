<?php

namespace App\Transformers\SalaryStatementDetail;

use App\Facades\MoneyFormat;
use App\Models\SalaryStatementDetail;
use Brick\Math\BigDecimal;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementDetail $salaryStatementDetail): array
    {
        $taxable = BigDecimal::of($salaryStatementDetail->taxable);
        $nontaxable = BigDecimal::of($salaryStatementDetail->nontaxable);
        $contribution = BigDecimal::of($salaryStatementDetail->contribution);
        $withholdingTax = BigDecimal::of($salaryStatementDetail->withholding_tax);
        $deduction = BigDecimal::of($salaryStatementDetail->deduction);
        $net = BigDecimal::of($salaryStatementDetail->net);

        $componentValues = MoneyFormat::numberFormatComponentValue($salaryStatementDetail->component_values, 4);
        $componentValueType = $componentValues['type'] ?? null;

        return [
            'row_number' => $salaryStatementDetail->row_number,
            'id' => $salaryStatementDetail->id,
            'formulable_type' => $salaryStatementDetail->formulable_type?->toArray(),
            'component_type' => $salaryStatementDetail->component_type?->toArray(),
            'component_name' => $salaryStatementDetail->component_name,
            'component_value_type' => $componentValueType,
            'component_values' => empty($componentValues) ? [] : [$componentValues],
            'taxable' => $taxable->isZero() ? '--' : MoneyFormat::numberFormat($taxable, 4),
            'nontaxable' => $nontaxable->isZero() ? '--' : MoneyFormat::numberFormat($nontaxable, 4),
            'contribution' => $contribution->isZero() ? '--' : MoneyFormat::numberFormat($contribution, 4),
            'withholding_tax' => $withholdingTax->isZero() ? '--' : MoneyFormat::numberFormat($withholdingTax, 4),
            'deduction' => $deduction->isZero() ? '--' : MoneyFormat::numberFormat($deduction, 4),
            'net' => $net->isZero() ? '--' : MoneyFormat::numberFormat($net, 4),
        ];
    }
}
