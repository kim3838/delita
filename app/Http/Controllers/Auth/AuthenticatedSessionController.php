<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AttemptToAuthenticate;
use App\Actions\Auth\EnsureLoginIsNotRateLimited;
use App\Actions\Auth\PrepareAuthenticatedSession;
use App\Actions\Auth\TwoFactorChallenge;
use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            TwoFactorChallenge::class,
            AttemptToAuthenticate::class,
            PrepareAuthenticatedSession::class
        ])->then(function ($request){
            return ResponseJson::successfulResponse([
                'two-factor-challenge' => false
            ]);
        });
    }

    public function hit(Request $request)
    {
        return ResponseJson::successfulResponse();
    }

    public function authenticated(Request $request)
    {
        \Illuminate\Support\Facades\Log::info([
            'method' => get_class() . '@' . __FUNCTION__,
            'line' => __LINE__,
            'session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
            'cookies' => $request->cookies->all(),
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

    public function sessions(Request $request)
    {
        $sessions = collect(
            DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
                ->where('user_id', $request->user()->getAuthIdentifier())
                ->orderBy('last_activity', 'desc')
                ->get()
        )->map(function ($session) use ($request) {
            $agent = $this->createAgent($session);

            return (object) [
                'agent' => [
                    'is_desktop' => $agent->isDesktop(),
                    'platform' => $agent->platform(),
                    'browser' => $agent->browser(),
                ],
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === $request->session()->getId(),
                'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
            ];
        });

        return ResponseJson::successfulResponse($sessions);
    }

    protected function createAgent($session)
    {
        return tap(new Agent, function ($agent) use ($session) {
            $agent->setUserAgent($session->user_agent);
        });
    }

    public function confirmedPasswordStatus(Request $request)
    {
        $passwordConfirmedAt = $request->session()->get('auth.password_confirmed_at', 0);
        $secondsPastAfterConfirmation = (time() - $passwordConfirmedAt);
        $passwordConfirmationTimeout = $request->input('seconds', config('auth.password_timeout', 900));

        return ResponseJson::successfulResponse([
            'confirmed' => $secondsPastAfterConfirmation < $passwordConfirmationTimeout,
        ]);
    }

    public function confirmPassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password:web']
        ]);

        $request->session()->put('auth.password_confirmed_at', time());

        return ResponseJson::successfulResponse();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        \Illuminate\Support\Facades\Log::info([
            'method' => get_class() . '@' . __FUNCTION__,
            'line' => __LINE__,
            'session' => collect(Session::all())->except(['_previous', '_flash'])->all(),
        ]);

        \Illuminate\Support\Facades\Log::info([
            'BEFORE logout: request user password_hash' => $request->user() ? $request->user()->getAuthPassword() : 'Not authenticated',
            'BEFORE logout: session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
            'BEFORE logout: cookies' => $request->cookies->all(),
        ]);

        Auth::guard('web')->logout();

        \Illuminate\Support\Facades\Log::info([
            'AFTER logout: request user password_hash' => $request->user() ? $request->user()->getAuthPassword() : 'Not authenticated',
            'AFTER logout: session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
            'AFTER logout: cookies' => $request->cookies->all(),
        ]);

        $request->session()->invalidate();

        \Illuminate\Support\Facades\Log::info([
            'AFTER session invalidate: request user password_hash' => $request->user() ? $request->user()->getAuthPassword() : 'Not authenticated',
            'AFTER session invalidate: session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
            'AFTER session invalidate: cookies' => $request->cookies->all(),
        ]);

        $request->session()->regenerateToken();

        \Illuminate\Support\Facades\Log::info([
            'AFTER session regenerateToken: request user password_hash' => $request->user() ? $request->user()->getAuthPassword() : 'Not authenticated',
            'AFTER session regenerateToken: session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
            'AFTER session regenerateToken: cookies' => $request->cookies->all(),
        ]);

        return ResponseJson::successfulResponse();
    }

    public function logoutOtherDevice(Request $request)
    {
        \Illuminate\Support\Facades\Log::info([
            'method' => get_class() . '@' . __FUNCTION__,
            'line' => __LINE__,
            'session' => collect(Session::all())->except(['_previous', '_flash'])->all(),
        ]);

        $request->validate([
            'password' => ['required', 'string', 'current_password:web'],
        ]);

        \Illuminate\Support\Facades\Log::info([
            'BEFORE logoutOtherDevices: request user password_hash' => $request->user() ? $request->user()->getAuthPassword() : 'Not authenticated',
            'BEFORE logoutOtherDevices: session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
            'BEFORE logoutOtherDevices: cookies' => $request->cookies->all(),
        ]);

        $result = Auth::guard('web')->logoutOtherDevices($request->password);

        \Illuminate\Support\Facades\Log::info([
            'AFTER logoutOtherDevices: request user password_hash' => $request->user() ? $request->user()->getAuthPassword() : 'Not authenticated',
            'AFTER logoutOtherDevices: session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
            'AFTER logoutOtherDevices: cookies' => $request->cookies->all(),
        ]);

        $this->deleteOtherSessionRecords($request);

        \Illuminate\Support\Facades\Log::info([
            'AFTER deleteOtherSessionRecords: request user password_hash' => $request->user() ? $request->user()->getAuthPassword() : 'Not authenticated',
            'AFTER deleteOtherSessionRecords: session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
            'AFTER deleteOtherSessionRecords: cookies' => $request->cookies->all(),
        ]);

        return ResponseJson::successfulResponse([
            'authenticatable' => $result
        ]);
    }

    protected function deleteOtherSessionRecords(Request $request)
    {
        \Illuminate\Support\Facades\Log::info([
            'method' => get_class() . '@' . __FUNCTION__,
            'line' => __LINE__,
            'session' => collect(Session::all())->except(['_previous', '_flash'])->all(),
            'cookies' => $request->cookies->all(),
            'request user id' => $request->user()->getAuthIdentifier(),
            'session getId' => $request->session()->getId(),
        ]);

        if (config('session.driver') !== 'database') {
            return;
        }

        DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->where('id', '!=', $request->session()->getId())
            ->delete();
    }
}
