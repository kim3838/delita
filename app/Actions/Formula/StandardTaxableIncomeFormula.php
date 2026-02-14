<?php

namespace App\Actions\Formula;

class StandardTaxableIncomeFormula
{
    public string $slug = 'standard-taxable-income';

    public function handle($data, $next)
    {
        _debug([
            'pipeline' => 'StandardTaxableIncomeFormula',
        ]);

        return $next($data);
    }
}
