<?php

namespace App\Actions\Formula;

class StandardCompensationTaxFormula
{
    public string $slug = 'standard-compensation-tax';

    public function handle($data, $next)
    {
        _debug([
            'pipeline' => 'StandardCompensationTaxFormula',
        ]);

        return $next($data);
    }
}
