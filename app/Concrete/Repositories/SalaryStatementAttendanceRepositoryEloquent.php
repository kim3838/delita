<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\SalaryStatementAttendanceRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\SalaryStatementAttendance;

class SalaryStatementAttendanceRepositoryEloquent extends BaseRepositoryEloquent implements SalaryStatementAttendanceRepository
{
    public function model(): string
    {
        return SalaryStatementAttendance::class;
    }
}
