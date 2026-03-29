<?php

namespace App\Transformers\Contribution;

use App\Facades\MoneyFormat;
use App\Models\SalaryStatementDetail;
use Brick\Math\BigDecimal;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use League\Fractal\TransformerAbstract;

class ExportTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementDetail $salaryStatementDetail): array
    {
        $contribution = BigDecimal::of($salaryStatementDetail->contribution);

        $componentValues = MoneyFormat::numberFormatComponentValue($salaryStatementDetail->component_values, 2);
        $componentValueType = $componentValues['type'] ?? null;

        $totalEmployerShare = Arr::get($componentValues, 'employer_share.total', MoneyFormat::numberFormat(0));

        return [
            'payroll_number' => $salaryStatementDetail->payroll_number,
            'year' => $salaryStatementDetail->payroll_year,
            'month' => $salaryStatementDetail->payroll_month,
            'month_readable' => Carbon::createFromDate(null, $salaryStatementDetail->payroll_month, 1)->format('F'),

            'employee_number' => $salaryStatementDetail->employee_number,
            'name' => $salaryStatementDetail->employee_full_name,

            'component_name' => $salaryStatementDetail->component_name,

            'employee_contribution' => MoneyFormat::numberFormat($contribution, 2),
            'employer_share' => $totalEmployerShare,
        ];
    }
}
