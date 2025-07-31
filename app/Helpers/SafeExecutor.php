<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Throwable;

class SafeExecutor
{
    public static function try(callable $callback): void
    {
        try {

            $callback();

        } catch (Throwable $throwable) {
            Log::error([
                'thrown' => get_class($throwable),
                'Exception instance?' => $throwable instanceof \Exception ? 'TRUE' : 'FALSE',
                'Error instance?' => $throwable instanceof \Error ? 'TRUE' : 'FALSE',
                'message' => $throwable->getMessage(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'request' => request()->url(),
                'session' => collect(session()->all())->except(['_previous', '_flash'])->all(),
                'cookies' => [
                    'decrypted' => request()->cookies->all(),
                    'raw' => CookieHelper::parseCookieString(request()->headers->get('cookie')),
                ],
            ]);
        }
    }
}
