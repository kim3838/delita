<?php

namespace App\Transformers\Payroll;

use App\Models\Payroll;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(Payroll $payroll): array
    {
        $totalSalaryStatementNet = BigDecimal::of((string)$payroll->total_salary_statement_net);
        $totalSalaryStatementBasicPay = BigDecimal::of((string)$payroll->total_basic_pay);
        $totalSalaryStatementNonstatutoryDeduction = BigDecimal::of((string)$payroll->total_nonstatutory_deduction);
        $totalSalaryStatementBasicGross = BigDecimal::of((string)$payroll->total_basic_gross);
        $totalEmployerContributionShare = BigDecimal::of((string)$payroll->total_employer_contribution_share);

        return [
            'row_number' => $payroll->row_number,
            'id' => $payroll->id,
            'ulid' => $payroll->ulid,
            'company_id' => $payroll->company_id,
            'number' => $payroll->number,
            'year' => $payroll->year,
            'month' => $payroll->month,
            'month_readable' => Carbon::createFromDate(null, $payroll->month, 1)->format('F'),
            'pay_frequency' => $payroll->pay_frequency?->toArray(),
            'frequency_sequence' => $payroll->frequency_sequence?->toArray(),
            'start_date' => $payroll->start_date?->toDateString(),
            'end_date' => $payroll->end_date?->toDateString(),
            'remarks' => $payroll->remarks,
            'status' => $payroll->status?->toArray(),

            'total_salary_statement_net' => $totalSalaryStatementNet->toScale(2, RoundingMode::HalfUp),
            'total_salary_statement_basic_pay' => $totalSalaryStatementBasicPay->toScale(2, RoundingMode::HalfUp),
            'total_salary_statement_nonstatutory_deduction' => $totalSalaryStatementNonstatutoryDeduction->toScale(2, RoundingMode::HalfUp),
            'total_salary_statement_basic_gross' => $totalSalaryStatementBasicGross->toScale(2, RoundingMode::HalfUp),
            'total_employer_contribution_share' => $totalEmployerContributionShare->toScale(2, RoundingMode::HalfUp),

            'date_range_readable' => $payroll->start_date->format('F j, Y') . ' - ' . $payroll->end_date->format('F j, Y'),
        ];
    }
}
