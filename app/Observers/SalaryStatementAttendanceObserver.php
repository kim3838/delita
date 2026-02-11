<?php

namespace App\Observers;

use App\Models\SalaryStatementAttendance;
use Illuminate\Support\Str;

class SalaryStatementAttendanceObserver
{
    public function creating(SalaryStatementAttendance $salaryStatementAttendance): bool
    {
        if (empty($salaryStatementAttendance->ulid)) {
            $salaryStatementAttendance->ulid = (string) Str::ulid();
        }

        return true;
    }
}
