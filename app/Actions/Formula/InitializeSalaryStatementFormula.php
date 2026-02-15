<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;

class InitializeSalaryStatementFormula
{
    public function handle(SalaryStatementContext $context, $next)
    {
        $shared = [
            'total_taxable' => 0
        ];

        foreach ($context->statementDetails as $detail) {
            $shared['total_taxable'] += $detail['taxable'];
        }

        $context->shared = $shared;

        return $next($context);
    }
}
