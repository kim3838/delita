<?php

namespace App\Actions\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PrepareAuthenticatedSession
{
    public function handle(Request $request, $next)
    {
        $debugEnabled = false;

        if($debugEnabled){

            Log::channel('auth')->info([
                'method' => basename(__FILE__) . '@' . __FUNCTION__,
                'line' => __LINE__,
                'user password_hash' => $request->user() ? $request->user()->getAuthPassword() : 'Not authenticated',
                'session' => collect($request->session()->all())->only(['_token'])->all(),
                'cookies' => $request->cookies->all(),
                'Request has session?' => ($request->hasSession() ? 'TRUE' : 'FALSE'),
                'BEFORE session regenerate:' => collect($request->session()->all())->only(['_token'])->all(),
                'BEFORE session regenerate: cookies' => $request->cookies->all(),
            ]);
        }

        /**
         * Update session's CSRF token
         * Which then use by the client application to update X-XSRF-TOKEN
         **/
        $request->session()->regenerate();

        if($request->user()){

            $request->session()->put([
                'password_hash_web' => $request->user()->getAuthPassword(),
                'password_hash_sanctum' => $request->user()->getAuthPassword(),
            ]);
        }

        if($debugEnabled){

            Log::channel('auth')->info([
                'AFTER session regenerate:' => collect($request->session()->all())->only(['_token'])->all(),
                'AFTER session regenerate: cookies' => $request->cookies->all(),
            ]);
        }

        return $next($request);
    }
}
