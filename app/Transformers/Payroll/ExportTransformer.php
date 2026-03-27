<?php

namespace App\Transformers\Payroll;

use App\Models\Payroll;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ExportTransformer extends TransformerAbstract
{
    public function transform(Payroll $payroll): array
    {
        $basicGross = BigDecimal::of($payroll->total_basic_gross);
        $otherGross = BigDecimal::of($payroll->total_other_gross);
        $taxable = BigDecimal::of($payroll->total_taxable);
        $nontaxable = BigDecimal::of($payroll->total_nontaxable);
        $contribution = BigDecimal::of($payroll->total_contribution);
        $employerContributionShare = BigDecimal::of($payroll->total_employer_contribution_share);
        $withholdingTax = BigDecimal::of($payroll->total_withholding_tax);
        $taxRefund = BigDecimal::of($payroll->total_tax_refund);
        $deduction = BigDecimal::of($payroll->total_deduction);
        $net = BigDecimal::of($payroll->total_net);

        $payrollStatus = $payroll->status;
        $payrollStatusReadable = empty($payrollStatus) ? 'Not found' : $payrollStatus->label();

        $payrollPayFrequency = $payroll->pay_frequency;
        $payrollPayFrequencyReadable = empty($payrollPayFrequency) ? 'Not found' : $payrollPayFrequency->label();

        $payrollPayFrequencySequence = $payroll->frequency_sequence;
        $payrollPayFrequencySequenceReadable = empty($payrollPayFrequencySequence) ? '' : $payrollPayFrequencySequence->label();

        $payrollStartDate = Carbon::parse($payroll->start_date);
        $payrollStartDateReadable = $payrollStartDate->format('M d, Y');

        $payrollEndDate = Carbon::parse($payroll->end_date);
        $payrollEndDateReadable = $payrollEndDate->format('M d, Y');

        return [
            'payroll_number' => $payroll->number,
            'status' => $payrollStatusReadable,
            'remarks' => $payroll->remarks,
            'year' => $payroll->year,
            'month' => $payroll->month,
            'month_readable' => Carbon::createFromDate(null, $payroll->month, 1)->format('F'),
            'pay_frequency' => $payrollPayFrequencyReadable,
            'frequency_sequence' => $payrollPayFrequencySequenceReadable,
            'start_date' => $payrollStartDateReadable,
            'end_date' => $payrollEndDateReadable,

            'basic_gross' => $basicGross->toScale(4, RoundingMode::HalfUp)->toString(),
            'other_gross' => $otherGross->toScale(4, RoundingMode::HalfUp)->toString(),
            'taxable' => $taxable->toScale(4, RoundingMode::HalfUp)->toString(),
            'nontaxable' => $nontaxable->toScale(4, RoundingMode::HalfUp)->toString(),
            'contribution' => $contribution->toScale(4, RoundingMode::HalfUp)->toString(),
            'employer_contribution_share' => $employerContributionShare->toScale(4, RoundingMode::HalfUp)->toString(),
            'withholding_tax' => $withholdingTax->toScale(4, RoundingMode::HalfUp)->toString(),
            'tax_refund' => $taxRefund->toScale(4, RoundingMode::HalfUp)->toString(),
            'deduction' => $deduction->toScale(4, RoundingMode::HalfUp)->toString(),
            'net' => $net->toScale(4, RoundingMode::HalfUp)->toString(),
        ];
    }
}
