<?php

namespace App\Actions\Common;

class DecodeArray
{
    public function handle($value, $next)
    {
        $value = json_decode($value, true);

        return $next($value);
    }
}
