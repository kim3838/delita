<?php

namespace App\Transformers\SalaryStatementDetail;

use App\Models\SalaryStatementDetail;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementDetail $salaryStatementDetail): array
    {
        $taxable = BigDecimal::of($salaryStatementDetail->taxable);
        $nontaxable = BigDecimal::of($salaryStatementDetail->nontaxable);
        $contribution = BigDecimal::of($salaryStatementDetail->contribution);
        $withholding_tax = BigDecimal::of($salaryStatementDetail->withholding_tax);
        $deduction = BigDecimal::of($salaryStatementDetail->deduction);
        $net = BigDecimal::of($salaryStatementDetail->net);

        $componentValues = $salaryStatementDetail->component_values;
        $componentValueType = $componentValues['type'] ?? null;

        return [
            'row_number' => $salaryStatementDetail->row_number,
            'id' => $salaryStatementDetail->id,
            'formulable_type' => $salaryStatementDetail->formulable_type?->toArray(),
            'component_type' => $salaryStatementDetail->component_type?->toArray(),
            'component_name' => $salaryStatementDetail->component_name,
            'component_value_type' => $componentValueType,
            'component_values' => empty($componentValues) ? [] : [$componentValues],
            'taxable' => $taxable->isZero() ? '--' : $taxable->toScale(2, RoundingMode::HalfUp),
            'nontaxable' => $nontaxable->isZero() ? '--' : $nontaxable->toScale(2, RoundingMode::HalfUp),
            'contribution' => $contribution->isZero() ? '--' : $contribution->toScale(2, RoundingMode::HalfUp),
            'withholding_tax' => $withholding_tax->isZero() ? '--' : $withholding_tax->toScale(2, RoundingMode::HalfUp),
            'deduction' => $deduction->isZero() ? '--' : $deduction->toScale(2, RoundingMode::HalfUp),
            'net' => $net->isZero() ? '--' : $net->toScale(2, RoundingMode::HalfUp),
        ];
    }
}
