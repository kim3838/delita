<?php

namespace App\Providers;

use App\Blueprint\AttendanceSplitterInterface;
use App\Blueprint\EnumInterface;
use App\Blueprint\PayrollServiceInterface;
use App\Concrete\AttendanceSplitter;
use App\Concrete\EnumConcrete;
use App\Concrete\PayrollServiceConcrete;
use App\Exceptions\UnexpectedException;
use App\Models\Company;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class DeferrableBindingsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public $bindings = [
        'enum' => EnumConcrete::class,
        EnumInterface::class => EnumConcrete::class,
    ];

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

    public function provides(): array
    {
        return [
            'enum',
            EnumInterface::class,
        ];
    }
}
