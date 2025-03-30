<?php

namespace App\Actions\Common;

class FilterInteger
{
    public function handle($value, $next)
    {
        $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);

        return $next($value);
    }
}
