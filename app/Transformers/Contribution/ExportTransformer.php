<?php

namespace App\Transformers\Contribution;

use App\Models\SalaryStatementDetail;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use League\Fractal\TransformerAbstract;

class ExportTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementDetail $salaryStatementDetail): array
    {
        $contribution = BigDecimal::of($salaryStatementDetail->contribution);

        $componentValues = $salaryStatementDetail->component_values;
        $componentValueType = $componentValues['type'] ?? null;

        $totalEmployerShare = BigDecimal::of(Arr::get($componentValues, 'employer_share.total', '0'));

        return [
            'payroll_number' => $salaryStatementDetail->payroll_number,
            'year' => $salaryStatementDetail->payroll_year,
            'month' => $salaryStatementDetail->payroll_month,
            'month_readable' => Carbon::createFromDate(null, $salaryStatementDetail->payroll_month, 1)->format('F'),

            'employee_number' => $salaryStatementDetail->employee_number,
            'name' => $salaryStatementDetail->employee_full_name,

            'component_type' => $salaryStatementDetail->component_type?->label() ?? '',
            'component_name' => $salaryStatementDetail->component_name,

            'employee_contribution' => $contribution->toScale(4, RoundingMode::HalfUp)->toString(),
            'employer_share' => $totalEmployerShare->toScale(4, RoundingMode::HalfUp)->toString(),
        ];
    }
}
