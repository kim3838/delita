<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\CompensationRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Compensation;

class CompensationRepositoryEloquent extends BaseRepositoryEloquent implements CompensationRepository
{
    public function model(): string
    {
        return Compensation::class;
    }

    public function store($attributes)
    {
        return $this->model::create($attributes);
    }
}
