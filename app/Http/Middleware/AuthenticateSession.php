<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Session\Middleware\AuthenticatesSessions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class AuthenticateSession implements AuthenticatesSessions
{
    protected bool $logger = false;
    protected bool $delay = false;

    /**
     * The authentication factory implementation.
     *
     * @var AuthFactory
     */
    protected AuthFactory $auth;

    /**
     * The callback that should be used to generate the authentication redirect path.
     *
     * @var callable
     */
    protected static $redirectToCallback;

    /**
     * Create a new middleware instance.
     *
     * @param AuthFactory $auth
     * @return void
     */
    public function __construct(AuthFactory $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($this->delay) {
            sleep(2);
        }

        if($this->logger){
            Log::channel('auth')->info([
                'method' => basename(__FILE__) . '@' . __FUNCTION__,
                'line' => __LINE__,
                'uri' => $request->getRequestUri(),
                'auth default driver' => Auth::getDefaultDriver(),
                'this->auth->getDefaultDriver' => $this->auth->getDefaultDriver(),
                'config default driver' => config('auth.defaults.guard'),
                'user password_hash' => $request->user() ? $request->user()->getAuthPassword() : 'Not authenticated',
                'session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
                'cookies' => $request->cookies->all(),
                'raw cookies' => $request->headers->get('cookie')
            ]);
        }

        if (! $request->hasSession() || ! $request->user() || ! $request->user()->getAuthPassword()) {
            if ($this->logger) {
                Log::channel('auth')->info([
                    'method' => basename(__FILE__) . '@' . __FUNCTION__,
                    'line' => __LINE__,
                    'next' => 'No session | No user | No user password'
                ]);
            }
            return $next($request);
        }

        if ($this->guard()->viaRemember()) {
            $passwordHash = explode('|', $request->cookies->get($this->guard()->getRecallerName()))[2] ?? null;

            if ($this->logger) {
                Log::channel('auth')->info([
                    'recalled name' => $this->auth->getRecallerName(),
                    'session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
                    'cookies' => $request->cookies->all(),
                    'user password_hash' => $request->user() ? $request->user()->getAuthPassword() : 'Not authenticated',
                    'cookie value' => $request->cookies->get($this->auth->getRecallerName()),
                    'cookie password hash' => $passwordHash
                ]);
            }

            if (! $passwordHash || ! hash_equals($request->user()->getAuthPassword(), $passwordHash)) {
                if ($this->logger) {
                    Log::channel('auth')->info([
                        'method' => basename(__FILE__) . '@' . __FUNCTION__,
                        'line' => __LINE__,
                        'call' => 'logout'
                    ]);
                }
                $this->logout($request);
            }
        }

        if (! $request->session()->has('password_hash_'.$this->auth->getDefaultDriver())) {
            if ($this->logger) {
                Log::channel('auth')->info([
                    'store password hash in session' => $request->user() ? $request->user()->getAuthPassword() : 'Not authenticated',
                    'session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
                    'cookies' => $request->cookies->all(),
                ]);
            }

            $this->storePasswordHashInSession($request);
        }

        if (! hash_equals($request->session()->get('password_hash_'.$this->auth->getDefaultDriver()), $request->user()->getAuthPassword())) {
            if ($this->logger) {
                Log::channel('auth')->info([
                    'method' => basename(__FILE__) . '@' . __FUNCTION__,
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

        if ($request->user() && $request->user()->status !== UserStatus::ACTIVE) {
            if ($this->logger) {
                Log::channel('auth')->info([
                    'method' => basename(__FILE__) . '@' . __FUNCTION__,
                    'line' => __LINE__,
                    'uri' => $request->getRequestUri(),
                    'user_id' => $request->user()->id,
                    'user_status' => $request->user()->status,
                    'reason' => 'User account is inactive',
                    'call' => 'logout'
                ]);
            }

            $this->logout($request, __('auth.inactive'));
        }

        return tap($next($request), function () use ($request) {
            if ($this->logger) {
                Log::channel('auth')->info([
                    'tapped' => $request->getRequestUri(),
                    'has_login_web__' => $request->session()->has('login_web_' . sha1(SessionGuard::class)),
                    'guard user' => ($this->guard()->user() ? $this->guard()->user()->getAuthIdentifier() : null)
                ]);
            }

            if (! is_null($this->guard()->user())
                && $request->session()->has('login_web_' . sha1(SessionGuard::class))
            ) {
                if ($this->logger) {
                    Log::channel('auth')->info([
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
     * @param Request $request
     * @return void
     */
    protected function storePasswordHashInSession(Request $request): void
    {
        if (! $request->user()) {
            return;
        }

        if ($this->logger) {
            Log::channel('auth')->info([
                'method' => basename(__FILE__) . '@' . __FUNCTION__,
                'line' => __LINE__,
                'uri' => $request->getRequestUri(),
                'auth default driver' => Auth::getDefaultDriver(),
                'this->auth->getDefaultDriver' => $this->auth->getDefaultDriver(),
                'config default driver' => config('auth.defaults.guard')
            ]);
        }

        if ($this->logger) {
            Log::channel('auth')->info([
                'BEFORE store password hash in session: session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
            ]);
        }

        $request->session()->put([
            'password_hash_web' => $request->user()->getAuthPassword(),
            'password_hash_sanctum' => $request->user()->getAuthPassword(),
        ]);

        if ($this->logger) {
            Log::channel('auth')->info([
                'AFTER store password hash in session: session' => collect($request->session()->all())->except(['_previous', '_flash'])->all(),
            ]);
        }
    }

    /**
     * Log the user out of the application.
     *
     * @param Request $request
     * @param string $message
     * @return void
     *
     * @throws AuthenticationException
     */
    protected function logout(Request $request, string $message = 'Unauthenticated.'): void
    {
        $this->guard()->logoutCurrentDevice();

        $request->session()->flush();

        if ($this->logger) {
            Log::channel('auth')->info([
                'method' => basename(__FILE__) . '@' . __FUNCTION__,
                'line' => __LINE__,
                'via remember?' => ($this->guard()->viaRemember() ? 'TRUE' : 'FALSE')
            ]);
        }

        if($this->guard()->viaRemember()){

            $rememberKey = 'remember_web_' . sha1(SessionGuard::class);
            Cookie::queue(Cookie::forget($rememberKey));
        }

        Cookie::queue(Cookie::forget('pc'));//Forget persist company
        Cookie::queue(Cookie::forget('pas'));//Forget persist account subscription

        throw new AuthenticationException(
            $message, [$this->auth->getDefaultDriver()], $this->redirectTo($request)
        );
    }

    /**
     * Get the guard instance that should be used by the middleware.
     *
     * @return AuthFactory|SessionGuard|Guard
     */
    protected function guard(): AuthFactory | SessionGuard | Guard
    {
        return $this->auth;
    }

    /**
     * Get the path the user should be redirected to when their session is not authenticated.
     *
     * @param Request $request
     * @return string|null
     */
    protected function redirectTo(Request $request): ?string
    {
        if (static::$redirectToCallback) {
            return call_user_func(static::$redirectToCallback, $request);
        }

        return null;
    }

    /**
     * Specify the callback that should be used to generate the redirect path.
     *
     * @param  callable  $redirectToCallback
     * @return void
     */
    public static function redirectUsing(callable $redirectToCallback): void
    {
        static::$redirectToCallback = $redirectToCallback;
    }

    protected function setUserTimezone(): void
    {
        if(auth()->check()){

            $timezone = auth()->user()->timezone ?? config('app.timezone');

            date_default_timezone_set($timezone);
        }
    }
}
