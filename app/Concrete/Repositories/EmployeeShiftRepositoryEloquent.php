<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeeShiftRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\EmployeeShift;

class EmployeeShiftRepositoryEloquent extends BaseRepositoryEloquent implements EmployeeShiftRepository
{
    public function model(): string
    {
        return EmployeeShift::class;
    }
}
