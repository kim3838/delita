<?php

namespace App\Transformers\Tax;

use App\Facades\MoneyFormat;
use App\Models\SalaryStatementDetail;
use Brick\Math\BigDecimal;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementDetail $salaryStatementDetail): array
    {
        $nontaxable = BigDecimal::of($salaryStatementDetail->nontaxable);
        $withholdingTax = BigDecimal::of($salaryStatementDetail->withholding_tax);

        $componentValues = MoneyFormat::numberFormatComponentValue($salaryStatementDetail->component_values, 2);
        $componentValueType = $componentValues['type'] ?? null;

        return [
            'row_number' => $salaryStatementDetail->row_number,
            'id' => $salaryStatementDetail->id,
            'salary_statement_id' => $salaryStatementDetail->salary_statement_id,
            'payroll' => [
                'number' => $salaryStatementDetail->payroll_number,
                'year' => $salaryStatementDetail->payroll_year,
                'month' => $salaryStatementDetail->payroll_month,
                'month_readable' => Carbon::createFromDate(null, $salaryStatementDetail->payroll_month, 1)->format('F'),
            ],
            'employee' => [
                'ulid' => $salaryStatementDetail->employee_ulid,
                'number' => $salaryStatementDetail->employee_number,
                'full_name' => $salaryStatementDetail->employee_full_name,
            ],
            'formulable_type' => $salaryStatementDetail->formulable_type?->toArray(),
            'component_type' => $salaryStatementDetail->component_type?->toArray(),
            'component_name' => $salaryStatementDetail->component_name,
            'component_value_type' => $componentValueType,
            'component_values' => empty($componentValues) ? [] : [$componentValues],
            'withholding_tax' => MoneyFormat::numberFormat($withholdingTax, 4),
            'nontaxable' => MoneyFormat::numberFormat($nontaxable,4),
        ];
    }
}
