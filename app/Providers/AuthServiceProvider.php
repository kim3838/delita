<?php

namespace App\Providers;

use App\Blueprint\Auth\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderBlueprint;
use App\Concrete\Auth\TwoFactorAuthenticationProvider;
use App\Mail\Auth\PasswordReset;
use App\Mail\Auth\EmailVerification;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;
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
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return new EmailVerification((object)array(
                'verification_url' => $url,
                'expire_in_minutes' => config('auth.verification.expire', 60),
            ))->to($notifiable->getEmailForVerification());
        });

        VerifyEmail::createUrlUsing(function(object $notifiable){
            return $this->emailVerificationUrl($notifiable);
        });

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return $this->passwordResetUrl($notifiable, $token);
        });

        ResetPassword::toMailUsing(function(object $notifiable, string $token){
            return new PasswordReset((object)array(
                'notifiable' => $notifiable,
                'password_reset_url' => $this->passwordResetUrl($notifiable, $token),
                'expire_in_minutes' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')
            ))->to($notifiable->getEmailForPasswordReset());
        });
    }

    public function passwordResetUrl(object $notifiable, string $token)
    {
        return config('app.frontend_url') . "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
    }

    public function emailVerificationUrl(object $notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            array(
                'id' => Crypt::encrypt($notifiable->getKey()),
                'hash' => Crypt::encrypt($notifiable->getEmailForVerification())
            )
        );
    }
}
