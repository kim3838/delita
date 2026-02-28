<?php

namespace App\Actions\Formula;

use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Concrete\SalaryStatementContext;
use App\Enums\PayFrequency;
use App\Enums\SemiMonthlySequence;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class InitializeSalaryStatementFormula
{
    public function handle(SalaryStatementContext $context, $next)
    {
        $totals = [];

        $totalTaxable = BigDecimal::zero();
        $totalDeduction = BigDecimal::zero();

        foreach ($context->statementDetails as $detail) {

            $totalTaxable = $totalTaxable->plus(BigDecimal::of((string)$detail['taxable']));
            $totalDeduction = $totalDeduction->plus(BigDecimal::of((string)$detail['deduction']));
        }

        $totals['taxable'] = (string)$totalTaxable->toScale(6, RoundingMode::HalfUp);
        $totals['deduction'] = (string)$totalDeduction->toScale(6, RoundingMode::HalfUp);

        $context->totals = $totals;

        /**
         * If semi-monthly 2nd half, fetch the first half salary statements
         **/
        if($context->flags['is_semimonthly_and_is_2nd_half']){

            $filters = (object)[
                'payroll_year' => $context->payroll->year,
                'payroll_month' => $context->payroll->month,
                'payroll_pay_frequency' => PayFrequency::SEMI_MONTHLY->value,
                'payroll_frequency_sequence' => SemiMonthlySequence::FIRST_HALF->value,
                'employee_ids' => [$context->employee->id]
            ];

            $context->additionalSalaryStatements = app(SalaryStatementRepository::class)
                ->list($filters, ['payroll'])
                ->sortBy('payroll_start_date');
        }

        /**
         * If weekly, check if payroll end date + 7 days is within the same year month
         **/
        if($context->flags['is_weekly_and_is_last_split_of_month']){

            $filters = (object)[
                'payroll_year' => $context->payroll->year,
                'payroll_month' => $context->payroll->month,
                'payroll_pay_frequency' => PayFrequency::WEEKLY->value,
                'employee_ids' => [$context->employee->id],
                'not_salary_statement_ids' => [$context->salaryStatement->id]
            ];

            $context->additionalSalaryStatements = app(SalaryStatementRepository::class)
                ->list($filters, ['payroll'])
                ->sortBy('payroll_start_date');
        }

        return $next($context);
    }
}
