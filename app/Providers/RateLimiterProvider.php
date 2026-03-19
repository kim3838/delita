<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimiterProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        RateLimiter::for('cloudflare_browser_rendering', function (object $job) {
            return Limit::perMinute(5);
        });

        RateLimiter::for('new_user_verification_notification', function (object $job) {
            return Limit::perMinute(30);
        });

        RateLimiter::for('new_user_credentials_notification', function (object $job) {
            return Limit::perMinute(15);
        });
    }
}
