<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;

class StandardBasicPayFormula
{
    public string $slug = 'standard-basic-pay';

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
