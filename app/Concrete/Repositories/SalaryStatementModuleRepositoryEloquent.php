<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\SalaryStatementModuleRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\SalaryStatementModule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class SalaryStatementModuleRepositoryEloquent extends BaseRepositoryEloquent implements SalaryStatementModuleRepository
{
    public function model(): string
    {
        return SalaryStatementModule::class;
    }

    public function list()
    {
        $filters = json_decode(Request::get('filters'));

        $queryBuilder = $this->model::getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("company_id"), $value);
            })
            ->orderBy('order', 'ASC');

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }
}
