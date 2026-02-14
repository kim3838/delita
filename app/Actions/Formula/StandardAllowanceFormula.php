<?php

namespace App\Actions\Formula;

class StandardAllowanceFormula
{
    public function handle($data, $next)
    {
        return $next($data);
    }
}
