<?php

namespace App\Transformers\Contribution;

use App\Facades\MoneyFormat;
use App\Models\SalaryStatementDetail;
use Brick\Math\BigDecimal;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementDetail $salaryStatementDetail): array
    {
        $contribution = BigDecimal::of($salaryStatementDetail->contribution);

        $componentValues = MoneyFormat::numberFormatComponentValue($salaryStatementDetail->component_values, 2);
        $componentValueType = $componentValues['type'] ?? null;

        $totalEmployerShare = Arr::get($componentValues, 'employer_share.total', MoneyFormat::numberFormat(0));

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
            'employee_contribution' => MoneyFormat::numberFormat($contribution, 2),
            'employer_share' => $totalEmployerShare,
        ];
    }
}
