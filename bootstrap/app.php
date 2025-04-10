<?php

use App\Facades\ResponseJson;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Response;
use Illuminate\Routing\Exceptions\BackedEnumCaseNotFoundException;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withBindings([
        'response_json' => fn() => new App\Concrete\JsonResponseScaffolder(),
        'fractal' => fn() => new App\Concrete\FractalTransformer(),
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();

        $middleware->append([
            \App\Http\Middleware\TransformQueryParameters::class
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
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
                \Illuminate\Support\Facades\Log::info([
                    ('thrown') => get_class($throwable),
                    'Exception instance?' => ($throwable instanceof Exception ? 'TRUE' : 'FALSE'),
                    'Error instance?' => ($throwable instanceof Error ? 'TRUE' : 'FALSE'),
                    'message' => $throwable->getMessage(),
                    'file' => $throwable->getFile(),
                    'line' => $throwable->getLine(),
                    'request' => Request::url(),
                    'session' => collect(Session::all())->except(['_previous', '_flash'])->all(),
                    'cookies' => request()->cookies->all()
                ]);
            }

            if($throwable instanceof Exception){
                $render = match(true){
                    $throwable instanceof NotFoundHttpException => ResponseJson::notFoundResponse(),
                    $throwable instanceof BackedEnumCaseNotFoundException => ResponseJson::notFoundResponse($throwable->getMessage()),
                    $throwable instanceof ModelNotFoundException => ResponseJson::notFoundResponse($throwable->getMessage()),
                    $throwable instanceof AuthorizationException && !$throwable->hasStatus() => ResponseJson::responseByCode(Response::HTTP_FORBIDDEN),
                    $throwable instanceof SuspiciousOperationException => ResponseJson::notFoundResponse('Bad hostname provided.'),
                    $throwable instanceof RecordsNotFoundException => ResponseJson::notFoundResponse(),
                    $throwable instanceof TokenMismatchException => ResponseJson::notAcceptableResponse(),
                    $throwable instanceof AuthenticationException => ResponseJson::unauthorizedResponse($throwable->getMessage()),
                    $throwable instanceof ValidationException => ResponseJson::unprocessableResponse($throwable->errors(), $throwable->getMessage()),
                    $throwable instanceof ThrottleRequestsException => ResponseJson::tooManyRequestsResponse(),
                    $throwable instanceof MethodNotAllowedHttpException => ResponseJson::methodNotAllowedResponse(),
                    $throwable instanceof InvalidArgumentException => ResponseJson::validationErrorResponse([], $throwable->getMessage()),
                    $throwable instanceof InvalidSignatureException => ResponseJson::unprocessableResponse([], $throwable->getMessage()),
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
