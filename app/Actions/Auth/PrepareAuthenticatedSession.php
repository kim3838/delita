<?php

namespace App\Actions\Auth;

use Illuminate\Http\Request;

class PrepareAuthenticatedSession
{
    public function handle(Request $request, $next)
    {
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return $next($request);
    }
}
