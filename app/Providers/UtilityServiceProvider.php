<?php

namespace App\Providers;

use App\Blueprint\AttendanceSplitterInterface;
use App\Blueprint\PrototypeInterface;
use App\Concrete\AttendanceSplitter;
use App\Concrete\PrototypeConcrete;
use App\Concrete\Utilities\UserSession as UserSessionUtility;
use App\Models\Company;
use Illuminate\Support\ServiceProvider;

class UtilityServiceProvider extends ServiceProvider
{
    public $singletons = [
        PrototypeInterface::class => PrototypeConcrete::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singletonIf(UserSessionUtility::class, function($app){
            return new UserSessionUtility();
        });

        $this->app->bind(AttendanceSplitterInterface::class, function ($app, $parameters) {

            $company = $parameters['company'] ?? null;

            if(!$company instanceof Company){
                throw new \InvalidArgumentException("company must be an instance of App\\Models\\Company");
            }

            return new AttendanceSplitter($company);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
