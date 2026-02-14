<?php

namespace App\Actions\Formula;

class StandardNontaxableIncomeFormula
{
    public string $slug = 'standard-nontaxable-income';

    public function handle($data, $next)
    {
        _debug([
            'pipeline' => 'StandardNontaxableIncomeFormula',
        ]);

        return $next($data);
    }
}
