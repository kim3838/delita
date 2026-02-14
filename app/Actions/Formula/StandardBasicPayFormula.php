<?php

namespace App\Actions\Formula;

class StandardBasicPayFormula
{
    public function handle($data, $next)
    {
        return $next($data);
    }
}
