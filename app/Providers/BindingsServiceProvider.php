<?php

namespace App\Providers;

use App\Blueprint\AttendanceSplitterInterface;
use App\Blueprint\EmployeeServiceInterface;
use App\Blueprint\LeaveServiceInterface;
use App\Blueprint\PayrollComponentServiceInterface;
use App\Blueprint\PayrollServiceInterface;
use App\Blueprint\PayslipServiceInterface;
use App\Blueprint\RequestInterface;
use App\Blueprint\WorkPeriodServiceInterface;
use App\Concrete\AttendanceSplitter;
use App\Concrete\EmployeeServiceConcrete;
use App\Concrete\LeaveService;
use App\Concrete\PayrollComponentServiceConcrete;
use App\Concrete\PayrollServiceConcrete;
use App\Concrete\PayslipServiceConcrete;
use App\Concrete\RequestConcrete;
use App\Concrete\WorkPeriodServiceConcrete;
use Illuminate\Support\ServiceProvider;

class BindingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singletonIf(RequestInterface::class, function($app){

            return new RequestConcrete();
        });

        $this->app->bind(WorkPeriodServiceInterface::class, function ($app) {

            return new WorkPeriodServiceConcrete();
        });

        $this->app->bind(PayrollComponentServiceInterface::class, function ($app) {

            return new PayrollComponentServiceConcrete();
        });

        $this->app->bind(LeaveServiceInterface::class, function ($app) {

            return new LeaveService();
        });

        $this->app->bind(AttendanceSplitterInterface::class, function ($app, $parameters) {

            list($company) = $parameters;

            return new AttendanceSplitter($company);
        });

        $this->app->bind(PayrollServiceInterface::class, function ($app, $parameters) {

            list($company) = $parameters;

            return new PayrollServiceConcrete($company);
        });

        $this->app->bind(EmployeeServiceInterface::class, function ($app, $parameters) {

            list($employee) = $parameters;

            return new EmployeeServiceConcrete($employee);
        });

        $this->app->bind(PayslipServiceInterface::class, function ($app, $parameters) {

            list($company) = $parameters;

            return new PayslipServiceConcrete($company);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

    }
}
