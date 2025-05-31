<?php

namespace App\Providers;

use App\Models\Compensation;
use App\Models\Deduction;
use App\Models\Employee;
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
            'compensation' => Compensation::class,
            'deduction' => Deduction::class
        ]);
    }
}
