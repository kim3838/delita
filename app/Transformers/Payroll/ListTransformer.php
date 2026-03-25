<?php

namespace App\Transformers\Payroll;

use App\Facades\MoneyFormat;
use App\Models\Payroll;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(Payroll $payroll): array
    {
        $totalBasicGross = MoneyFormat::numberFormat($payroll->total_basic_gross);
        $totalOtherGross = MoneyFormat::numberFormat($payroll->total_other_gross);
        $totalTaxable = MoneyFormat::numberFormat($payroll->total_taxable);
        $totalNontaxable = MoneyFormat::numberFormat($payroll->total_nontaxable);
        $totalContribution = MoneyFormat::numberFormat($payroll->total_contribution);
        $totalEmployerContributionShare = MoneyFormat::numberFormat($payroll->total_employer_contribution_share);
        $totalWithholdingTax = MoneyFormat::numberFormat($payroll->total_withholding_tax);
        $totalTaxRefund = MoneyFormat::numberFormat($payroll->total_tax_refund);
        $totalDeduction = MoneyFormat::numberFormat($payroll->total_deduction);
        $totalNet = MoneyFormat::numberFormat($payroll->total_net);

        $totalBasicGrossFormatted = MoneyFormat::toLocale($payroll->total_basic_gross, $payroll->company_currency_code);
        $totalOtherGrossFormatted = MoneyFormat::toLocale($payroll->total_other_gross, $payroll->company_currency_code);
        $totalTaxableFormatted = MoneyFormat::toLocale($payroll->total_taxable, $payroll->company_currency_code);
        $totalNontaxableFormatted = MoneyFormat::toLocale($payroll->total_nontaxable, $payroll->company_currency_code);
        $totalContributionFormatted = MoneyFormat::toLocale($payroll->total_contribution, $payroll->company_currency_code);
        $totalEmployerContributionShareFormatted = MoneyFormat::toLocale($payroll->total_employer_contribution_share, $payroll->company_currency_code);
        $totalWithholdingTaxFormatted = MoneyFormat::toLocale($payroll->total_withholding_tax, $payroll->company_currency_code);
        $totalTaxRefundFormatted = MoneyFormat::toLocale($payroll->total_tax_refund, $payroll->company_currency_code);
        $totalDeductionFormatted = MoneyFormat::toLocale($payroll->total_deduction, $payroll->company_currency_code);
        $totalNetFormatted = MoneyFormat::toLocale($payroll->total_net, $payroll->company_currency_code);

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

            'total_basic_gross' => $totalBasicGross,
            'total_other_gross' => $totalOtherGross,
            'total_taxable' => $totalTaxable,
            'total_nontaxable' => $totalNontaxable,
            'total_contribution' => $totalContribution,
            'total_employer_contribution_share' => $totalEmployerContributionShare,
            'total_withholding_tax' => $totalWithholdingTax,
            'total_tax_refund' => $totalTaxRefund,
            'total_deduction' => $totalDeduction,
            'total_net' => $totalNet,

            'total_basic_gross_formatted' => $totalBasicGrossFormatted,
            'total_other_gross_formatted' => $totalOtherGrossFormatted,
            'total_taxable_formatted' => $totalTaxableFormatted,
            'total_nontaxable_formatted' => $totalNontaxableFormatted,
            'total_contribution_formatted' => $totalContributionFormatted,
            'total_employer_contribution_share_formatted' => $totalEmployerContributionShareFormatted,
            'total_withholding_tax_formatted' => $totalWithholdingTaxFormatted,
            'total_tax_refund_formatted' => $totalTaxRefundFormatted,
            'total_deduction_formatted' => $totalDeductionFormatted,
            'total_net_formatted' => $totalNetFormatted,

            'date_range_readable' => $payroll->start_date->format('M d, Y') . ' - ' . $payroll->end_date->format('M d, Y'),
        ];
    }
}
