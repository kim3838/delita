<?php

namespace App\Actions\Auth;

use App\Blueprint\Auth\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderBlueprint;
use App\Events\Auth\TwoFactorAuthenticationConfirmed;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ConfirmTwoFactorAuthentication
{
    public function __construct(
        protected TwoFactorAuthenticationProviderBlueprint $provider
    ){}

    /**
     * Confirm the two factor authentication configuration for the user.
     *
     * @param \App\Models\User $user
     * @param string $code
     * @return void
     */
    public function __invoke(User $user, $code)
    {
        if (empty($user->two_factor_secret) ||
            empty($code) ||
            !$this->provider->verify(decrypt($user->two_factor_secret), $code)) {
            throw ValidationException::withMessages([
                'code' => [__('The provided two factor authentication code was invalid.')],
            ])->errorBag('confirmTwoFactorAuthentication');
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        TwoFactorAuthenticationConfirmed::dispatch($user);
    }
}
