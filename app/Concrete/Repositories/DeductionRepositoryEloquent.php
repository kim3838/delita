<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\DeductionRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Deduction;
use Illuminate\Support\Facades\DB;

class DeductionRepositoryEloquent extends BaseRepositoryEloquent implements DeductionRepository
{
    public function model(): string
    {
        return Deduction::class;
    }

    public function selection($filters)
    {
        $queryBuilder = $this->model->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("deductions.company_id"), $value);
            })
            ->when(isset($filters->assignable), function ($builder) use($filters){
                $builder->where(DB::raw("deductions.assignable"), intval($filters->assignable));
            })
            ->select([
                'deductions.id AS id',
                'deductions.code AS code',
                'deductions.name AS name',
                'deductions.type AS type',
                'deductions.assignable AS assignable',
            ])
            ->orderBy('order', 'ASC');

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }
}
