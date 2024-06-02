<?php

namespace App\Providers;

use App\Blueprint\Auth\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderBlueprint;
use App\Concrete\Auth\TwoFactorAuthenticationProvider;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\ServiceProvider;
use PragmaRX\Google2FA\Google2FA;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TwoFactorAuthenticationProviderBlueprint::class, function($app){
            return new TwoFactorAuthenticationProvider(
                $app->make(Google2FA::class),
                $app->make(Repository::class)
            );
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
