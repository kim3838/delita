<?php

namespace App\Actions\Auth;

use Illuminate\Http\Request;

class PrepareAuthenticatedSession
{
    public function handle(Request $request, $next)
    {
        \Illuminate\Support\Facades\Log::info([
            'method' => get_class() . '@' . __FUNCTION__,
            'line' => __LINE__,
            'session' => collect($request->session()->all())->only(['_token'])->all(),
        ]);

        \Log::debug(print_r([
            'Request has session?' => ($request->hasSession() ? 'TRUE' : 'FALSE'),
            'BEFORE session regenerate:' => collect($request->session()->all())->only(['_token'])->all(),
            'BEFORE session regenerate: cookies' => $request->cookies->all(),
        ], true));

        //Update session's CSRF token
        //Which then use by the client application to update X-XSRF-TOKEN
        $request->session()->regenerate();

        \Log::debug(print_r([
            'AFTER session regenerate:' => collect($request->session()->all())->only(['_token'])->all(),
            'AFTER session regenerate: cookies' => $request->cookies->all(),
        ], true));

        return $next($request);
    }
}
