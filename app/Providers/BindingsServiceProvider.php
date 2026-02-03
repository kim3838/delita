<?php

namespace App\Providers;

use App\Blueprint\AttendanceSplitterInterface;
use App\Blueprint\PayrollServiceInterface;
use App\Concrete\AttendanceSplitter;
use App\Concrete\PayrollServiceConcrete;
use App\Exceptions\UnexpectedException;
use App\Models\Company;
use Illuminate\Support\ServiceProvider;

class BindingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AttendanceSplitterInterface::class, function ($app, $parameters) {

            list($company) = $parameters;

            if(!$company instanceof Company){
                throw new UnexpectedException("Company must be an instance of App\\Models\\Company @ Bindings service provider [" . __LINE__ . "]");
            }

            return new AttendanceSplitter($company);
        });

        $this->app->bind(PayrollServiceInterface::class, function ($app, $parameters) {

            list($company) = $parameters;

            if(!$company instanceof Company){
                throw new UnexpectedException("Company must be an instance of App\\Models\\Company @ Bindings service provider [" . __LINE__ . "]");
            }

            return new PayrollServiceConcrete($company);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

    }
}
