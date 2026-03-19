<?php

namespace App\Actions\Auth;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AttemptToAuthenticate
{
    /**
     * @throws AuthenticationException
     */
    public function handle(Request $request, $next)
    {
        $debugEnabled = false;

        $identifierField = filter_var($request->input('identifier'), FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'name';

        if($debugEnabled){

            Log::channel('auth')->info([
                'method' => basename(__FILE__) . '@' . __FUNCTION__,
                'line' => __LINE__,
                'session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
                'cookies' => $request->cookies->all(),
                'attempt' => array(
                    $identifierField => $request->input('identifier'),
                    'password' => $request->input('password'),
                    'remember' => $request->boolean('remember')
                )
            ]);
        }

        // First check if user exists and has active status
        $user = User::query()
            ->where($identifierField, $request->input('identifier'))
            ->first();

        if ($user && $user->status !== UserStatus::ACTIVE) {
            RateLimiter::hit(Throttle::key($request));
            throw new AuthenticationException(__('auth.inactive'));
        }


        if(! Auth::attempt([
            $identifierField => $request->input('identifier'),
            'password' => $request->input('password'),
        ], $request->boolean('remember'))){

            RateLimiter::hit(Throttle::key($request));

            throw new AuthenticationException(__('auth.failed'));
        }

        RateLimiter::clear(Throttle::key($request));

        return $next($request);
    }
}
