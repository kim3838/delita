<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AttemptToAuthenticate;
use App\Actions\Auth\EnsureLoginIsNotRateLimited;
use App\Actions\Auth\PrepareAuthenticatedSession;
use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Support\Facades\Session;
use Jenssegers\Agent\Agent;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        return Pipeline::send($request)->through([
            EnsureLoginIsNotRateLimited::class,
            AttemptToAuthenticate::class,
            PrepareAuthenticatedSession::class
        ])->then(function ($request){
            return ResponseJson::successfulResponse([
                'two-factor-challenge' => false
            ]);
        });
    }

    public function authenticated(Request $request)
    {
        \Illuminate\Support\Facades\Log::info([
            'file' => __FILE__,
            'authenticated' => $request->user()->getAuthIdentifier(),
            'session' => json_encode(Session::all()),
        ]);

        $authenticated = (object)array_merge(
            $request->user()->toArray(), [
            'two_factor_enabled' => !is_null($request->user()->two_factor_secret),
            'two_factor_confirmed' => !is_null($request->user()->two_factor_confirmed_at),
        ]);

        return ResponseJson::successfulResponse($authenticated);
    }

    public function testPost(Request $request)
    {
        return ResponseJson::successfulResponse();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return ResponseJson::successfulResponse();
    }
}
