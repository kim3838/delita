<?php

namespace App\Transformers\SalaryStatement;

use App\Enums\PayFrequency;
use App\Enums\PayrollStatus;
use App\Enums\SemiMonthlySequence;
use App\Models\SalaryStatement;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ExportTransformer extends TransformerAbstract
{
    public function transform(SalaryStatement $salaryStatement): array
    {
        $basicGross = BigDecimal::of($salaryStatement->total_basic_gross);
        $otherGross = BigDecimal::of($salaryStatement->total_other_gross);
        $taxable = BigDecimal::of($salaryStatement->taxable);
        $nontaxable = BigDecimal::of($salaryStatement->nontaxable);
        $contribution = BigDecimal::of($salaryStatement->contribution);
        $withholding_tax = BigDecimal::of($salaryStatement->withholding_tax);
        $deduction = BigDecimal::of($salaryStatement->deduction);
        $net = BigDecimal::of($salaryStatement->net);

        $payrollStatus = PayrollStatus::tryFrom($salaryStatement->payroll_status);
        $payrollStatusReadable = empty($payrollStatus) ? 'Not found' : $payrollStatus->label();

        $payrollMonthReadable = Carbon::createFromDate(null, $salaryStatement->payroll_month, 1)->format('F');

        $payrollPayFrequency = PayFrequency::tryFrom($salaryStatement->payroll_pay_frequency);
        $payrollPayFrequencyReadable = empty($payrollPayFrequency) ? 'Not found' : $payrollPayFrequency->label();

        $payrollPayFrequencySequence = SemiMonthlySequence::tryFrom($salaryStatement->payroll_frequency_sequence);
        $payrollPayFrequencySequenceReadable = empty($payrollPayFrequencySequence) ? '' : $payrollPayFrequencySequence->label();

        $payrollStartDate = Carbon::parse($salaryStatement->payroll_start_date);
        $payrollStartDateReadable = $payrollStartDate->format('M j, Y');

        $payrollEndDate = Carbon::parse($salaryStatement->payroll_end_date);
        $payrollEndDateReadable = $payrollEndDate->format('M j, Y');

        return [
            'payroll_number' => $salaryStatement->payroll_number,
            'status' => $payrollStatusReadable,
            'remarks' => $salaryStatement->payroll_remarks,
            'year' => $salaryStatement->payroll_year,
            'month' => $salaryStatement->payroll_month,
            'month_readable' => $payrollMonthReadable,
            'pay_frequency' => $payrollPayFrequencyReadable,
            'frequency_sequence' => $payrollPayFrequencySequenceReadable,
            'start_date' => $payrollStartDateReadable,
            'end_date' => $payrollEndDateReadable,

            'employee_number' => $salaryStatement->employee_number,
            'name' => $salaryStatement->employee_full_name,

            'type' => $salaryStatement->type?->label(),
            'is_paid' => $salaryStatement->is_paid ? 'Yes' : 'No',

            'basic_gross' => $basicGross->toScale(2, RoundingMode::HalfUp),
            'other_gross' => $otherGross->toScale(2, RoundingMode::HalfUp),
            'taxable' => $taxable->toScale(2, RoundingMode::HalfUp),
            'nontaxable' => $nontaxable->toScale(2, RoundingMode::HalfUp),
            'contribution' => $contribution->toScale(2, RoundingMode::HalfUp),
            'withholding_tax' => $withholding_tax->toScale(2, RoundingMode::HalfUp),
            'deduction' => $deduction->toScale(2, RoundingMode::HalfUp),
            'net' => $net->toScale(2, RoundingMode::HalfUp),
        ];
    }
}
