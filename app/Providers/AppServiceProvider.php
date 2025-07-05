<?php

namespace App\Providers;

use App\Models\Compensation;
use App\Models\Deduction;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\IncomeTax;
use App\Models\SalaryStatementModule;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'user' => User::class,
            'employee' => Employee::class,
            'designation' => Designation::class,
            'compensation' => Compensation::class,
            'deduction' => Deduction::class,
            'income_tax' => IncomeTax::class,
            'salary_statement_module' => SalaryStatementModule::class,
        ]);
    }
}
