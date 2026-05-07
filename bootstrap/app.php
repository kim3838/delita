<?php

use App\Concrete\FractalTransformer;
use App\Concrete\JsonResponseScaffolder;
use App\Concrete\LogContext;
use App\Concrete\MoneyFormatter;
use App\Concrete\TimeZoneConverter;
use App\Facades\ResponseJson;
use App\Helpers\CookieHelper;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\RequestBoot;
use App\Http\Middleware\TransformQueryParameters;
use App\Models\ThrownLog;
use App\Notifications\ErrorLogNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Routing\Exceptions\BackedEnumCaseNotFoundException;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withBindings([
        'response_json' => fn() => new JsonResponseScaffolder(),
        'fractal' => fn() => new FractalTransformer(),
        'time_zone_converter' => fn() => new TimeZoneConverter(),
        'money_format' => fn() => new MoneyFormatter(),
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();

        //Disable encryption for a subset of custom-generated cookies
        $middleware->encryptCookies([
            'pc',//Persist company
            'pas'//Persist account subscription
        ]);

        $middleware->append([
            RequestBoot::class,
            TransformQueryParameters::class
        ]);

        $middleware->alias([
            'verified' => EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontReport([]);

        $exceptions->stopIgnoring([HttpException::class]);

        $exceptions->report(function(Throwable $throwable) {})->stop();

        $exceptions->render(function(Throwable $throwable){

            $logExempt = _is_instance_of_any($throwable, [
                AccessDeniedHttpException::class,
                AuthenticationException::class,
            ]);

            if(!$logExempt){

                $logAction = _is_instance_of_any($throwable, [
                    ValidationException::class
                ]) ? 'notice' : 'error';

                ThrownLog::query()->create([
                    'thrown' => get_class($throwable),
                    'is_exception' => $throwable instanceof Exception,
                    'is_error' => $throwable instanceof Error,
                    'message' => $throwable->getMessage(),
                    'file' => $throwable->getFile(),
                    'line' => $throwable->getLine(),
                    'request' => Request::url(),
                ]);

                $mailErrorLog = true;

                if($mailErrorLog && App::environment('production')){

                    NotificationFacade::route('mail',
                        ['no-reply@kunsel-erp.com' => 'Kim De Guzman']
                    )->notify(new ErrorLogNotification(new LogContext(
                        get_class($throwable),
                        $throwable instanceof Exception,
                        $throwable instanceof Error,
                        $throwable->getMessage(),
                        $throwable->getFile(),
                        $throwable->getLine(),
                        Request::url()
                    )));
                }

                Log::channel('error')->{$logAction}([
                    ('thrown') => get_class($throwable),
                    'Exception instance?' => ($throwable instanceof Exception ? 'TRUE' : 'FALSE'),
                    'Error instance?' => ($throwable instanceof Error ? 'TRUE' : 'FALSE'),
                    'message' => $throwable->getMessage(),
                    'file' => $throwable->getFile(),
                    'line' => $throwable->getLine(),
                    'request' => Request::url(),
                    'session' => collect(Session::all())->except(['_previous', '_flash'])->all(),
                    'cookies' => [
                        'decrpyted' => request()->cookies->all(),
                        'raw' => CookieHelper::parseCookieString(request()->headers->get('cookie'))
                    ]
                ]);
            }

            if($throwable instanceof Exception){
                $render = match(true){
                    $throwable instanceof NotFoundHttpException,
                    $throwable instanceof RecordsNotFoundException => ResponseJson::notFoundResponse(),
                    $throwable instanceof BackedEnumCaseNotFoundException,
                    $throwable instanceof ModelNotFoundException => ResponseJson::notFoundResponse($throwable->getMessage()),
                    $throwable instanceof AuthorizationException && !$throwable->hasStatus() => ResponseJson::responseByCode(Response::HTTP_FORBIDDEN),
                    $throwable instanceof SuspiciousOperationException => ResponseJson::notFoundResponse('Bad hostname provided.'),
                    $throwable instanceof TokenMismatchException => ResponseJson::notAcceptableResponse(),
                    $throwable instanceof AuthenticationException => ResponseJson::unauthorizedResponse($throwable->getMessage()),
                    $throwable instanceof ValidationException => ResponseJson::validationErrorResponse($throwable->errors(), $throwable->getMessage()),
                    $throwable instanceof ThrottleRequestsException => ResponseJson::tooManyRequestsResponse(),
                    $throwable instanceof MethodNotAllowedHttpException => ResponseJson::methodNotAllowedResponse(),
                    $throwable instanceof InvalidArgumentException => ResponseJson::validationErrorResponse([], $throwable->getMessage()),
                    $throwable instanceof InvalidSignatureException,
                    $throwable instanceof AccessDeniedHttpException => ResponseJson::forbiddenResponse($throwable->getMessage()),
                    default => $throwable,
                };

                if($render instanceof \Illuminate\Http\JsonResponse){
                    return $render;
                }
            }

            return ResponseJson::serverErrorResponse();
        });
    })->create();
