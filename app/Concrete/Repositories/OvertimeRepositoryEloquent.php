<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\OvertimeRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Overtime;

class OvertimeRepositoryEloquent extends BaseRepositoryEloquent implements OvertimeRepository
{
    public function model(): string
    {
        return Overtime::class;
    }
}
