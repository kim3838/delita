<?php

namespace App\Providers;

use App\Actions\Formula\InitializeSalaryStatementFormula;
use App\Actions\Formula\ManualDeductionFormula;
use App\Actions\Formula\ManualEarningFormula;
use App\Actions\Formula\Standard13thMonthFormula;
use App\Actions\Formula\StandardAbsenceFormula;
use App\Actions\Formula\StandardAllowanceFormula;
use App\Actions\Formula\StandardBasicPayFormula;
use App\Actions\Formula\StandardWithHoldingTaxCompensationFormula;
use App\Actions\Formula\StandardNetIncomeFormula;
use App\Actions\Formula\StandardNontaxableIncomeFormula;
use App\Actions\Formula\StandardOvertimeFormula;
use App\Actions\Formula\StandardPagIBIGContributionFormula;
use App\Actions\Formula\StandardPhilhealthContributionFormula;
use App\Actions\Formula\StandardSSSEmployedContributionFormula;
use App\Actions\Formula\StandardTardinessFormula;
use App\Actions\Formula\StandardTaxableIncomeFormula;
use App\Actions\Formula\StandardUndertimeFormula;
use Illuminate\Support\ServiceProvider;

class FormulaBindingsServiceProvider extends ServiceProvider
{
    public $bindings = [

        /**
         * Per day
         **/
        'standard-basic-pay' => StandardBasicPayFormula::class,
        'standard-allowance' => StandardAllowanceFormula::class,
        'standard-overtime' => StandardOvertimeFormula::class,

        /**
         * Salary statement level
         **/
        'initialize-salary-statement' => InitializeSalaryStatementFormula::class,
        'manual-earning' => ManualEarningFormula::class,
        'standard-sss-employed-contribution' => StandardSSSEmployedContributionFormula::class,
        'standard-philhealth-contribution' => StandardPhilhealthContributionFormula::class,
        'standard-pag-ibig-contribution' => StandardPagIBIGContributionFormula::class,
        'standard-13th-month' => Standard13thMonthFormula::class,
        'standard-taxable-income'=> StandardTaxableIncomeFormula::class,
        'standard-nontaxable-income' => StandardNontaxableIncomeFormula::class,
        'standard-withholding-tax-compensation' => StandardWithHoldingTaxCompensationFormula::class,
        'manual-deduction' => ManualDeductionFormula::class,
        'standard-net-income' => StandardNetIncomeFormula::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    public function provides(): array
    {
        return [
            'standard-basic-pay',
            'standard-allowance',
            'standard-overtime',

            'initialize-salary-statement',
            'standard-absence',
            'standard-undertime',
            'standard-tardiness',
            'standard-sss-employed-contribution',
            'standard-philhealth-contribution',
            'standard-pag-ibig-contribution',
            'standard-13th-month',
            'standard-taxable-income',
            'standard-nontaxable-income',
            'standard-withholding-tax-compensation',
            'standard-net-income',
        ];
    }
}
