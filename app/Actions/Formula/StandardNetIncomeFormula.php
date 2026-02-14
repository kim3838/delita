<?php

namespace App\Actions\Formula;

class StandardNetIncomeFormula
{
    public string $slug = 'standard-net-income';

    public function handle($data, $next)
    {
        _debug([
            'pipeline' => 'StandardNetIncomeFormula',
        ]);

        return $next($data);
    }
}
