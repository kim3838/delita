<?php

namespace App\Observers;

use App\Models\SalaryStatement;
use Illuminate\Support\Str;

class SalaryStatementObserver
{
    public function creating(SalaryStatement $salaryStatement): bool
    {
        if (empty($payroll->ulid)) {
            $salaryStatement->ulid = (string) Str::ulid();
        }

        return true;
    }
}
