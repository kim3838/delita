<?php

namespace App\Actions\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class EnsureLoginIsNotRateLimited
{
    public function handle(Request $request, $next)
    {
        if(RateLimiter::tooManyAttempts(Throttle::key($request), 5)){

            event(new Lockout($request));

            $seconds = RateLimiter::availableIn(Throttle::key($request));

            throw new ThrottleRequestsException(trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]));
        }

        return $next($request);
    }
}
