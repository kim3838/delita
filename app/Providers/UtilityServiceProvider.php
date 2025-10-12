<?php

namespace App\Providers;

use App\Concrete\Utilities\UserSession as UserSessionUtility;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class UtilityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singletonIf(UserSessionUtility::class,function(Application $app){
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
