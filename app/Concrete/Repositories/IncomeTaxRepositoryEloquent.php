<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\IncomeTaxRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\IncomeTax;
use App\Traits\PayrollComponentChain;
use Illuminate\Support\Facades\DB;

class IncomeTaxRepositoryEloquent extends BaseRepositoryEloquent implements IncomeTaxRepository
{
    use PayrollComponentChain;

    public function model(): string
    {
        return IncomeTax::class;
    }

    public function selection($filters)
    {
        $queryBuilder = $this->model->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("income_taxes.company_id"), $value);
            })
            ->when(isset($filters->assignable), function ($builder) use($filters){
                $builder->where(DB::raw("income_taxes.assignable"), intval($filters->assignable));
            })
            ->select([
                'income_taxes.id AS id',
                'income_taxes.code AS code',
                'income_taxes.name AS name',
                'income_taxes.type AS type',
                'income_taxes.assignable AS assignable',
            ])
            ->orderBy('order', 'ASC');

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }

    public function delete($id)
    {
        $model = $this->model::findOrfail($id);

        $this->deleteEmployeeAssignedComponentable('income_tax', $model->id);

        return $model->delete();
    }
}
