<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

        $exceptions->report(function (Exception $exception) {})->stop();

        $exceptions->render(function(Exception $exception, \Illuminate\Http\Request $request){
            \Illuminate\Support\Facades\Log::info([
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'request' => $request->url(),
                'session' => json_encode(Session::all())
            ]);

            return ResponseJson::serverErrorResponse();
        });


    })->create();
