<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;

class StandardPhilhealthContributionFormula
{
    public string $slug = 'standard-philhealth-contribution';

    public function handle(SalaryStatementContext $context, $next)
    {
        _debug([
            'pipeline' => 'StandardPhilhealthContributionFormula',
        ]);

        return $next($context);
    }
}
