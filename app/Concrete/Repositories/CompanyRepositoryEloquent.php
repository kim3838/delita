<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\CompanyRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Company;

class CompanyRepositoryEloquent extends BaseRepositoryEloquent implements CompanyRepository
{
    public function model(): string
    {
        return Company::class;
    }
}
