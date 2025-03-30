<?php

namespace App\Actions\Common;

class NullOnEmptyString
{
    public function handle($value, $next)
    {
        $emptyString = trim($value ?? '') == '';

        $value = $emptyString ? null : $value;

        return $next($value);
    }
}
