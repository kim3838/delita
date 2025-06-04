<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\DeductionRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Deduction;

class DeductionRepositoryEloquent extends BaseRepositoryEloquent implements DeductionRepository
{
    public function model(): string
    {
        return Deduction::class;
    }
}
