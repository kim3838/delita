<?php

namespace App\Providers;

use App\Blueprint\Auth\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderBlueprint;
use App\Concrete\Auth\TwoFactorAuthenticationProvider;
use App\Mail\Auth\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
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
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return $this->passwordResetLink($notifiable, $token);
        });

        ResetPassword::toMailUsing(function(object $notifiable, string $token){
            return (new PasswordReset((object)array(
                'notifiable' => $notifiable,
                'password_reset_url' => $this->passwordResetLink($notifiable, $token),
                'expire_in_minutes' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')
            )))->to($notifiable->getEmailForPasswordReset());
        });
    }

    public function passwordResetLink(object $notifiable, string $token)
    {
        return config('app.frontend_url') . "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
    }
}
