<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\IncomeTaxRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\IncomeTax;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class IncomeTaxRepositoryEloquent extends BaseRepositoryEloquent implements IncomeTaxRepository
{
    public function model(): string
    {
        return IncomeTax::class;
    }

    public function selection()
    {
        $filters = json_decode(Request::get('filters'));

        $queryBuilder = $this->model->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("income_taxes.company_id"), $value);
            })
            ->when(isset($filters->assignable), function ($builder) use($filters){
                $builder->where(DB::raw("income_taxes.assignable"), intval($filters->assignable));
            })
            ->select([
                'income_taxes.id AS id',
                'income_taxes.name AS name',
                'income_taxes.type AS type',
                'income_taxes.assignable AS assignable',
            ])
            ->orderBy('order', 'ASC');

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }
}
