<?php

namespace App\Providers;

use App\Actions\Formula\StandardAllowanceFormula;
use App\Actions\Formula\StandardBasicPayFormula;
use App\Actions\Formula\StandardCompensationTaxFormula;
use App\Actions\Formula\StandardNetIncomeFormula;
use App\Actions\Formula\StandardNontaxableIncomeFormula;
use App\Actions\Formula\StandardOvertimeFormula;
use App\Actions\Formula\StandardPagIBIGContributionFormula;
use App\Actions\Formula\StandardPhilhealthContributionFormula;
use App\Actions\Formula\StandardSSSEmployedContributionFormula;
use App\Actions\Formula\StandardTaxableIncomeFormula;
use Illuminate\Support\ServiceProvider;

class FormulaBindingsServiceProvider extends ServiceProvider
{
    public $bindings = [
        'standard-basic-pay' => StandardBasicPayFormula::class,
        'standard-allowance' => StandardAllowanceFormula::class,
        'standard-overtime' => StandardOvertimeFormula::class,

        'standard-sss-employed-contribution' => StandardSSSEmployedContributionFormula::class,
        'standard-philhealth-contribution' => StandardPhilhealthContributionFormula::class,
        'standard-pag-ibig-contribution' => StandardPagIBIGContributionFormula::class,
        'standard-taxable-income'=> StandardTaxableIncomeFormula::class,
        'standard-nontaxable-income' => StandardNontaxableIncomeFormula::class,
        'standard-compensation-tax' => StandardCompensationTaxFormula::class,
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
        ];
    }
}
