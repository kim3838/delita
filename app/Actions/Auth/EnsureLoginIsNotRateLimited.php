<?php

namespace App\Actions\Auth;

use App\Facades\ResponseJson;
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

            $availableIn = RateLimiter::availableIn(Throttle::key($request));

            $rateLimitExceedsMessage = "Too many attempts, try again in $availableIn second" . ($availableIn > 0 ? 's' : '') . ".";

            return ResponseJson::tooManyRequestsResponse($rateLimitExceedsMessage);
        }

        return $next($request);
    }
}
