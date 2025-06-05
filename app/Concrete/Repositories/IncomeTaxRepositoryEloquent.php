<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\IncomeTaxRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\IncomeTax;

class IncomeTaxRepositoryEloquent extends BaseRepositoryEloquent implements IncomeTaxRepository
{
    public function model(): string
    {
        return IncomeTax::class;
    }
}
