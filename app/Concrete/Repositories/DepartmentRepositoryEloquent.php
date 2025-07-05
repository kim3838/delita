<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\DepartmentRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Department;

class DepartmentRepositoryEloquent extends BaseRepositoryEloquent implements DepartmentRepository
{
    public function model(): string
    {
        return Department::class;
    }
}
