<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\SalaryStatement;

class SalaryStatementRepositoryEloquent extends BaseRepositoryEloquent implements SalaryStatementRepository
{
    public function model(): string
    {
        return SalaryStatement::class;
    }
}
