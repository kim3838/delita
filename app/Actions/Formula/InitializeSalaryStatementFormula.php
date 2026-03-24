<?php

namespace App\Actions\Formula;

use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Concrete\SalaryStatementContext;
use App\Enums\Compensation as CompensationEnum;
use App\Enums\Formulable;
use App\Enums\FormulableComponentSubType;
use App\Enums\PayFrequency;
use App\Enums\SemiMonthlySequence;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class InitializeSalaryStatementFormula
{
    public function handle(SalaryStatementContext $context, $next)
    {
        $debugEnabled = false;
        $totals = [];

        $totalNonTaxable = BigDecimal::zero();
        $totalTaxable = BigDecimal::zero();
        $totalDeduction = BigDecimal::zero();

        $totalNontaxableBonus = BigDecimal::of('0');
        $totalTaxableBonus = BigDecimal::of('0');

        foreach ($context->statementDetails as $detail) {

            $totalNonTaxable = $totalNonTaxable->plus(BigDecimal::of((string)$detail['nontaxable']));
            $totalTaxable = $totalTaxable->plus(BigDecimal::of((string)$detail['taxable']));
            $totalDeduction = $totalDeduction->plus(BigDecimal::of((string)$detail['deduction']));

            $totalNontaxableBonus = $totalNontaxableBonus->plus(BigDecimal::of((string)($detail['nontaxable_bonus'] ?? '0')));
            $totalTaxableBonus = $totalTaxableBonus->plus(BigDecimal::of((string)($detail['taxable_bonus'] ?? '0')));
        }

        if($debugEnabled){

            _debug([
                'Initialize' => 'Salary Statement Formula',
                'nontaxable' => (string)$totalNonTaxable->toScale(6, RoundingMode::HalfUp),
                'taxable' => (string)$totalTaxable->toScale(6, RoundingMode::HalfUp),
                'deduction' => (string)$totalDeduction->toScale(6, RoundingMode::HalfUp),
                'nontaxable_bonus' => (string)$totalNontaxableBonus->toScale(6, RoundingMode::HalfUp),
                'taxable_bonus' => (string)$totalTaxableBonus->toScale(6, RoundingMode::HalfUp)
            ]);
        }

        /**
         * Example bonus
         **/
        $customBonus = $totalNontaxableBonus->isGreaterThan(BigDecimal::zero()) || $totalTaxableBonus->isGreaterThan(BigDecimal::zero());

        if($customBonus){

            $context->statementDetails[] = [
                'id' => null,
                'formulable_type' => Formulable::EARNINGS->value,
                'component_type' => CompensationEnum::BENEFIT->value,
                'component_sub_type' => FormulableComponentSubType::NONSTATUTORY_BENEFIT_BONUS->value,
                'component_name' => 'Bonus',
                'component_values' => null,
                'taxable' => $totalTaxableBonus->toScale(6, RoundingMode::HalfUp)->toString(),
                'nontaxable' => $totalNontaxableBonus->toScale(6, RoundingMode::HalfUp)->toString(),
                'deduction' => 0.0,
                'contribution' => 0.0,
                'withholding_tax' => 0.0,
                'net' => 0.0,
            ];
        }

        $totals['nontaxable'] = (string)$totalNonTaxable->toScale(6, RoundingMode::HalfUp);
        $totals['taxable'] = (string)$totalTaxable->toScale(6, RoundingMode::HalfUp);
        $totals['deduction'] = (string)$totalDeduction->toScale(6, RoundingMode::HalfUp);

        $totals['nontaxable_bonus'] = (string)$totalNontaxableBonus->toScale(6, RoundingMode::HalfUp);
        $totals['taxable_bonus'] = (string)$totalTaxableBonus->toScale(6, RoundingMode::HalfUp);

        $context->totals = $totals;

        /**
         * If semi-monthly 2nd half, fetch the first half salary statements
         **/
        if($context->flags['is_semimonthly_and_is_2nd_half']){

            $filters = (object)[
                'payroll_year' => $context->payroll->year,
                'payroll_month' => $context->payroll->month,
                'payroll_pay_frequency' => PayFrequency::SEMIMONTHLY->value,
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
