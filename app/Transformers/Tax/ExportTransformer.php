<?php

namespace App\Transformers\Tax;

use App\Facades\MoneyFormat;
use App\Models\SalaryStatementDetail;
use Brick\Math\BigDecimal;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ExportTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementDetail $salaryStatementDetail): array
    {
        $nontaxable = BigDecimal::of($salaryStatementDetail->nontaxable);
        $withholdingTax = BigDecimal::of($salaryStatementDetail->withholding_tax);

        $componentValues = MoneyFormat::numberFormatComponentValue($salaryStatementDetail->component_values, 2);
        $componentValueType = $componentValues['type'] ?? null;

        return [
            'payroll_number' => $salaryStatementDetail->payroll_number,
            'year' => $salaryStatementDetail->payroll_year,
            'month' => $salaryStatementDetail->payroll_month,
            'month_readable' => Carbon::createFromDate(null, $salaryStatementDetail->payroll_month, 1)->format('F'),

            'employee_number' => $salaryStatementDetail->employee_number,
            'name' => $salaryStatementDetail->employee_full_name,

            'component_type' => $salaryStatementDetail->component_type?->label(),
            'component_name' => $salaryStatementDetail->component_name,

            'withholding_tax' => MoneyFormat::numberFormat($withholdingTax),
            'nontaxable' => MoneyFormat::numberFormat($nontaxable),
        ];
    }
}
