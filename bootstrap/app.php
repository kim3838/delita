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
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withBindings([
        'response_json' => fn() => new App\Concrete\JsonResponseScaffolder()
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontReport([]);

        $exceptions->stopIgnoring([HttpException::class]);

        $exceptions->report(function(Throwable $throwable) {})->stop();

        $exceptions->render(function(Throwable $throwable){
            \Illuminate\Support\Facades\Log::info([
                ('thrown') => get_class($throwable),
                'Exception instance?' => ($throwable instanceof Exception ? 'TRUE' : 'FALSE'),
                'Error instance?' => ($throwable instanceof Error ? 'TRUE' : 'FALSE'),
                'message' => $throwable->getMessage(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'request' => Request::url(),
                'session' => json_encode(Session::all())
            ]);

            if($throwable instanceof Exception){
                $render = match(true){
                    $throwable instanceof NotFoundHttpException => ResponseJson::notFoundResponse(),
                    $throwable instanceof BackedEnumCaseNotFoundException => ResponseJson::notFoundResponse($throwable->getMessage()),
                    $throwable instanceof ModelNotFoundException => ResponseJson::notFoundResponse($throwable->getMessage()),
                    $throwable instanceof AuthorizationException && !$throwable->hasStatus() => ResponseJson::responseByCode(Response::HTTP_FORBIDDEN),
                    $throwable instanceof SuspiciousOperationException => ResponseJson::notFoundResponse('Bad hostname provided.'),
                    $throwable instanceof RecordsNotFoundException => ResponseJson::notFoundResponse(),
                    $throwable instanceof TokenMismatchException => ResponseJson::notAcceptableResponse(),
                    $throwable instanceof AuthenticationException => ResponseJson::unauthorizedResponse(),
                    $throwable instanceof ValidationException => ResponseJson::unprocessableResponse(),
                    $throwable instanceof ThrottleRequestsException => ResponseJson::tooManyRequestsResponse(),
                    default => $throwable,
                };

                if($render instanceof \Illuminate\Http\JsonResponse){
                    return $render;
                }
            }

            return ResponseJson::serverErrorResponse();
        });
    })->create();
