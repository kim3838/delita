<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;

class StandardPagIBIGContributionFormula
{
    public string $slug = 'standard-pag-ibig-contribution';

    public function handle(SalaryStatementContext $context, $next)
    {
        _debug([
            'pipeline' => 'StandardPagIBIGContributionFormula',
        ]);

        return $next($context);
    }
}
