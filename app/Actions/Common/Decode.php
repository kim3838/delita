<?php

namespace App\Actions\Common;

class Decode
{
    public function handle($value, $next)
    {
        $value = json_decode($value);

        return $next($value);
    }
}
