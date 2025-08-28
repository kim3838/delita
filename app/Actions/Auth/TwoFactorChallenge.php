<?php

namespace App\Actions\Auth;

use App\Events\Auth\TwoFactorAuthenticationChallenged;
use App\Facades\ResponseJson;
use App\Traits\TwoFactorAuthenticatable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class TwoFactorChallenge
{
    /**
     * @throws AuthenticationException
     */
    public function handle(Request $request, $next)
    {
        $identifierField = filter_var($request->input('identifier'), FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'name';

        $user = tap(
         Auth::getProvider()->retrieveByCredentials([
            $identifierField => $request->input('identifier')
        ]), function ($user) use ($request) {

            if (!$user || !Auth::getProvider()->validateCredentials($user, ['password' => $request->input('password')])) {

                RateLimiter::hit(Throttle::key($request));

                throw new AuthenticationException(__('auth.failed'));
            }
        });

        Log::channel('auth')->info([
            'method' => basename(__FILE__) . '@' . __FUNCTION__,
            'line' => __LINE__,
            'user' => $user->toArray()
        ]);

        RateLimiter::clear(Throttle::key($request));

        $twoFactorEnabled = !is_null(optional($user)->two_factor_secret);
        $twoFactorConfirmed = !is_null(optional($user)->two_factor_confirmed_at);
        $hasTwoFactorAuthenticatableTrait = in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user));
        $twoFactorChallenge = array_product([$twoFactorEnabled, $twoFactorConfirmed, $hasTwoFactorAuthenticatableTrait]);

        Log::channel('auth')->info([
            'method' => basename(__FILE__) . '@' . __FUNCTION__,
            'line' => __LINE__,
            'two factor enabled' => ($twoFactorEnabled ? 'TRUE' : 'FALSE'),
            'two factor confirmed' => ($twoFactorConfirmed ? 'TRUE' : 'FALSE'),
            'challenge the authenticating user?>' => ($twoFactorChallenge ? 'TRUE' : 'FALSE'),
        ]);

        if ($twoFactorChallenge) {

            $request->session()->put([
                'login.id' => $user->getAuthIdentifier(),
                'login.remember' => $request->boolean('remember')
            ]);

            Log::channel('auth')->info([
                'method' => basename(__FILE__) . '@' . __FUNCTION__,
                'line' => __LINE__,
                'session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
                'cookies' => $request->cookies->all(),
            ]);

            TwoFactorAuthenticationChallenged::dispatch($user);

            return ResponseJson::successfulResponse([
                'two_factor_challenge' => true
            ]);
        }

        return $next($request);
    }
}
