<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\PayFrequencyRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\PayFrequency;
use Illuminate\Support\Facades\DB;

class PayFrequencyRepositoryEloquent extends BaseRepositoryEloquent implements PayFrequencyRepository
{
    public function model(): string
    {
        return PayFrequency::class;
    }

    public function list($filters)
    {
        $queryBuilder = $this->model->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("pay_frequencies.company_id"), $value);
            })
            ->select([
                'pay_frequencies.*'
            ])
            ->orderBy('order', 'ASC');

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }

    public function selection($filters)
    {
        $queryBuilder = $this->model->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("pay_frequencies.company_id"), $value);
            })
            ->select([
                'pay_frequencies.id AS id',
                'pay_frequencies.code AS code',
                'pay_frequencies.type AS type',
            ])
            ->orderBy('order', 'ASC');

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }
}
