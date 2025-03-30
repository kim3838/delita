<?php

namespace App\Actions\Common;

class FilterBoolean
{
    public function handle($value, $next)
    {
        $value = filter_var($value, FILTER_VALIDATE_BOOL, array('flags' => FILTER_NULL_ON_FAILURE));

        return $next($value);
    }
}
