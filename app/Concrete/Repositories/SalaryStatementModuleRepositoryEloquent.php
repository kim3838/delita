<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\SalaryStatementModuleRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\SalaryStatementModule;
use Illuminate\Support\Facades\DB;

class SalaryStatementModuleRepositoryEloquent extends BaseRepositoryEloquent implements SalaryStatementModuleRepository
{
    public function model(): string
    {
        return SalaryStatementModule::class;
    }

    public function list($filters)
    {
        $queryBuilder = $this->model::getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("company_id"), $value);
            })
            ->orderBy('order', 'ASC');

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }
}
