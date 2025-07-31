<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeeContactRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\EmployeeContact;

class EmployeeContactRepositoryEloquent extends BaseRepositoryEloquent implements EmployeeContactRepository
{
    public function model(): string
    {
        return EmployeeContact::class;
    }

    public function show($employeeId)
    {
        $queryBuilder = $this->model::where('employee_id', $employeeId);

        return $queryBuilder->first();
    }

    public function update($employeeId, $attributes)
    {
        $model = $this->show($employeeId);

        $model->update($attributes);

        return $model;
    }
}
