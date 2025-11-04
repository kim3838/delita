<?php

namespace App\Providers;

use App\Blueprint\AttendanceSplitterInterface;
use App\Blueprint\PrototypeInterface;
use App\Concrete\AttendanceSplitter;
use App\Concrete\PrototypeConcrete;
use App\Concrete\Utilities\UserSession as UserSessionUtility;
use App\Exceptions\UnexpectedException;
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

            list($company) = $parameters;

            if(!$company instanceof Company){
                throw new UnexpectedException("Company must be an instance of App\\Models\\Company C.UtilityServiceProvider [" . __LINE__ . "]");
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
