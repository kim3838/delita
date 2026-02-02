<?php

namespace App\Providers;

use App\Blueprint\PrototypeInterface;
use App\Concrete\PrototypeConcrete;
use App\Concrete\Utilities\UserSession as UserSessionUtility;
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
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
