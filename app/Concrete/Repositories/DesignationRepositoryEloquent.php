<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\DesignationRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Designation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class DesignationRepositoryEloquent extends BaseRepositoryEloquent implements DesignationRepository
{
    public function model(): string
    {
        return Designation::class;
    }

    public function list()
    {
        $filters = json_decode(Request::get('filters'));

        $queryBuilder = $this->model->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("designations.company_id"), $value);
            })
            ->select([
                'designations.id AS id',
                'designations.company_id AS company_id',
                'designations.name AS name'
            ]);

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }

    public function selection()
    {
        $filters = json_decode(Request::get('filters'));

        $queryBuilder = $this->model->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("designations.company_id"), $value);
            })
            ->select([
                'designations.id AS id',
                'designations.company_id AS company_id',
                'designations.name AS name',
            ])
            ->orderBy('name', 'ASC');

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }
}
