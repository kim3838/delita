<?php

namespace App\Actions\Formula;

class StandardOvertimeFormula
{
    public function handle($data, $next)
    {
        return $next($data);
    }
}
