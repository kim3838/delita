<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;

class StandardAllowanceFormula
{
    public string $slug = 'standard-allowance';

    public function handle(SalaryStatementContext $context, $next)
    {
        $debugEnabled = false;

        if($debugEnabled){
            _debug([
                'Formula slug' => $this->slug,
            ]);
        }

        return $next($context);
    }
}
