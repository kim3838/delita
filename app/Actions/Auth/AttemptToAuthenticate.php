<?php

namespace App\Actions\Auth;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AttemptToAuthenticate
{
    public function handle(Request $request, $next)
    {
        $identifierField = filter_var($request->input('identifier'), FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'name';

        \Illuminate\Support\Facades\Log::info([
            'method' => get_class() . '@' . __FUNCTION__,
            'line' => __LINE__,
            'session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
            'cookies' => $request->cookies->all(),
            'attempt' => array(
                $identifierField => $request->input('identifier'),
                'password' => $request->input('password'),
                'remember' => $request->boolean('remember')
            )
        ]);

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
