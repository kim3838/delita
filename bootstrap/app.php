<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
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

        $exceptions->report(function (Exception $e) {})->stop();

        $exceptions->render(function(Exception $exception, \Illuminate\Http\Request $request){
            \Illuminate\Support\Facades\Log::info([
                'Render Request' => $request->url(),
                'Render Exception Class' => get_class($exception),
                'Render Exception Message' => $exception->getMessage()
            ]);

            return response()->json([
                'message' => $exception->getMessage()
            ]);
        });


    })->create();
