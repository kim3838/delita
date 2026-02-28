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
        $totalBasicGross = BigDecimal::of((string)$payroll->total_basic_gross);
        $totalOtherGross = BigDecimal::of((string)$payroll->total_other_gross);
        $totalTaxable = BigDecimal::of((string)$payroll->total_taxable);
        $totalNontaxable = BigDecimal::of((string)$payroll->total_nontaxable);
        $totalContribution = BigDecimal::of((string)$payroll->total_contribution);
        $totalEmployerContributionShare = BigDecimal::of((string)$payroll->total_employer_contribution_share);
        $totalTaxWithheld = BigDecimal::of((string)$payroll->total_tax_withheld);
        $totalDeduction = BigDecimal::of((string)$payroll->total_deduction);
        $totalNet = BigDecimal::of((string)$payroll->total_net);

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

            'total_basic_gross' => $totalBasicGross->toScale(2, RoundingMode::HalfUp),
            'total_other_gross' => $totalOtherGross->toScale(2, RoundingMode::HalfUp),
            'total_taxable' => $totalTaxable->toScale(2, RoundingMode::HalfUp),
            'total_nontaxable' => $totalNontaxable->toScale(2, RoundingMode::HalfUp),
            'total_contribution' => $totalContribution->toScale(2, RoundingMode::HalfUp),
            'total_employer_contribution_share' => $totalEmployerContributionShare->toScale(2, RoundingMode::HalfUp),
            'total_tax_withheld' => $totalTaxWithheld->toScale(2, RoundingMode::HalfUp),
            'total_deduction' => $totalDeduction->toScale(2, RoundingMode::HalfUp),
            'total_net' => $totalNet->toScale(2, RoundingMode::HalfUp),

            'date_range_readable' => $payroll->start_date->format('F j, Y') . ' - ' . $payroll->end_date->format('F j, Y'),
        ];
    }
}
