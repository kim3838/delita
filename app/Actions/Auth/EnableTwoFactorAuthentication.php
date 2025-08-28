<?php

namespace App\Actions\Auth;

use App\Blueprint\Auth\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderBlueprint;
use App\Concrete\Auth\TwoFactorRecoveryCode;
use App\Events\Auth\TwoFactorAuthenticationEnabled;
use App\Models\User;
use Illuminate\Support\Collection;

class EnableTwoFactorAuthentication
{
    public function __construct(
        protected TwoFactorAuthenticationProviderBlueprint $provider
    ){}

    /**
     * Enable two factor authentication for the user.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function __invoke(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => encrypt($this->provider->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return TwoFactorRecoveryCode::generate();
            })->all())),
        ])->save();

        TwoFactorAuthenticationEnabled::dispatch($user);
    }
}
