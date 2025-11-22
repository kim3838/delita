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

    public function show($identifier)
    {
        $queryBuilder = $this->model::query()->where('employee_id', $identifier);

        return $queryBuilder->first();
    }

    public function update($identifier, $attributes)
    {
        $model = $this->show($identifier);

        $model->update($attributes);

        return $model;
    }
}
