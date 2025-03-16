<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Session\Middleware\AuthenticatesSessions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class AuthenticateSession implements AuthenticatesSessions
{
    protected $logger = false;
    protected $delay = false;

    /**
     * The authentication factory implementation.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * The callback that should be used to generate the authentication redirect path.
     *
     * @var callable
     */
    protected static $redirectToCallback;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(AuthFactory $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->delay) {
            sleep(2);
        }

        if($this->logger){
            \Illuminate\Support\Facades\Log::info([
                'method' => get_class() . '@' . __FUNCTION__,
                'line' => __LINE__,
                'uri' => $request->getRequestUri(),
                'auth default driver' => Auth::getDefaultDriver(),
                'this->auth->getDefaultDriver' => $this->auth->getDefaultDriver(),
                'config default driver' => config('auth.defaults.guard'),
                'user password_hash' => $request->user() ? $request->user()->getAuthPassword() : 'Not authenticated',
                'session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
                'cookies' => $request->cookies->all()
            ]);
        }

        if (! $request->hasSession() || ! $request->user() || ! $request->user()->getAuthPassword()) {
            if ($this->logger) {
                \Illuminate\Support\Facades\Log::info([
                    'method' => get_class() . '@' . __FUNCTION__,
                    'line' => __LINE__,
                    'next' => 'No session | No user | No user password'
                ]);
            }
            return $next($request);
        }

        if ($this->guard()->viaRemember()) {
            $passwordHash = explode('|', $request->cookies->get($this->guard()->getRecallerName()))[2] ?? null;

            if ($this->logger) {
                \Log::debug(print_r([
                    'recalled name' => $this->auth->getRecallerName(),
                    'session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
                    'cookies' => $request->cookies->all(),
                    'user password_hash' => $request->user() ? $request->user()->getAuthPassword() : 'Not authenticated',
                    'cookie value' => $request->cookies->get($this->auth->getRecallerName()),
                    'cookie password hash' => $passwordHash
                ], true));
            }

            if (! $passwordHash || ! hash_equals($request->user()->getAuthPassword(), $passwordHash)) {
                if ($this->logger) {
                    \Illuminate\Support\Facades\Log::info([
                        'method' => get_class() . '@' . __FUNCTION__,
                        'line' => __LINE__,
                        'call' => 'logout'
                    ]);
                }
                $this->logout($request);
            }
        }

        if (! $request->session()->has('password_hash_'.$this->auth->getDefaultDriver())) {
            if ($this->logger) {
                \Illuminate\Support\Facades\Log::info([
                    'store password hash in session' => $request->user() ? $request->user()->getAuthPassword() : 'Not authenticated',
                    'session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
                    'cookies' => $request->cookies->all(),
                ]);
            }
            $this->storePasswordHashInSession($request);
        }

        if (! hash_equals($request->session()->get('password_hash_'.$this->auth->getDefaultDriver()), $request->user()->getAuthPassword())) {
            if ($this->logger) {
                \Illuminate\Support\Facades\Log::info([
                    'method' => get_class() . '@' . __FUNCTION__,
                    'line' => __LINE__,
                    'uri' => $request->getRequestUri(),
                    'user password_hash' => $request->user() ? $request->user()->getAuthPassword() : 'Not authenticated',
                    'session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
                    'cookies' => $request->cookies->all(),
                    'call' => 'logout'
                ]);
            }

            $this->logout($request);
        }

        return tap($next($request), function () use ($request) {
            if ($this->logger) {
                \Illuminate\Support\Facades\Log::info([
                    'tapped' => $request->getRequestUri(),
                    'tapped parameters' => $request->all(),
                    'guard user' => ($this->guard()->user() ? $this->guard()->user()->getAuthIdentifier() : null)
                ]);
            }

            if (! is_null($this->guard()->user())) {
                if ($this->logger) {
                    \Illuminate\Support\Facades\Log::info([
                        'store password hash in session' => $request->user() ? $request->user()->getAuthPassword() : 'Not authenticated',
                        'session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
                        'cookies' => $request->cookies->all(),
                    ]);
                }
                $this->storePasswordHashInSession($request);
            }
        });
    }

    /**
     * Store the user's current password hash in the session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function storePasswordHashInSession($request)
    {
        if (! $request->user()) {
            return;
        }

        if ($this->logger) {
            \Illuminate\Support\Facades\Log::info([
                'method' => get_class() . '@' . __FUNCTION__,
                'line' => __LINE__,
                'uri' => $request->getRequestUri(),
                'auth default driver' => Auth::getDefaultDriver(),
                'this->auth->getDefaultDriver' => $this->auth->getDefaultDriver(),
                'config default driver' => config('auth.defaults.guard')
            ]);
        }

        if ($this->logger) {
            \Illuminate\Support\Facades\Log::info([
                'BEFORE store password hash in session: session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
            ]);
        }

        $request->session()->put([
            'password_hash_web' => $request->user()->getAuthPassword(),
            'password_hash_sanctum' => $request->user()->getAuthPassword(),
        ]);

        if ($this->logger) {
            \Illuminate\Support\Facades\Log::info([
                'AFTER store password hash in session: session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
            ]);
        }
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function logout($request)
    {
        $this->guard()->logoutCurrentDevice();

        $request->session()->flush();

        if ($this->logger) {
            \Illuminate\Support\Facades\Log::info([
                'method' => get_class() . '@' . __FUNCTION__,
                'line' => __LINE__,
                'via remember?' => ($this->guard()->viaRemember() ? 'TRUE' : 'FALSE')
            ]);
        }

        $message = $this->guard()->viaRemember() ? 'Please try again.' : 'Unauthenticated.';

        throw new AuthenticationException(
            $message, [$this->auth->getDefaultDriver()], $this->redirectTo($request)
        );
    }

    /**
     * Get the guard instance that should be used by the middleware.
     *
     * @return \Illuminate\Contracts\Auth\Factory|\Illuminate\Contracts\Auth\Guard
     */
    protected function guard()
    {
        return $this->auth;
    }

    /**
     * Get the path the user should be redirected to when their session is not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo(Request $request)
    {
        if (static::$redirectToCallback) {
            return call_user_func(static::$redirectToCallback, $request);
        }
    }

    /**
     * Specify the callback that should be used to generate the redirect path.
     *
     * @param  callable  $redirectToCallback
     * @return void
     */
    public static function redirectUsing(callable $redirectToCallback)
    {
        static::$redirectToCallback = $redirectToCallback;
    }
}
