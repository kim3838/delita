<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\SalaryStatementAttendanceDetailRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\SalaryStatementAttendanceDetail;

class SalaryStatementAttendanceDetailRepositoryEloquent extends BaseRepositoryEloquent implements SalaryStatementAttendanceDetailRepository
{
    public function model(): string
    {
        return SalaryStatementAttendanceDetail::class;
    }
}
