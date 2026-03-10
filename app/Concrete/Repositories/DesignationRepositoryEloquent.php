<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\DesignationRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Designation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DesignationRepositoryEloquent extends BaseRepositoryEloquent implements DesignationRepository
{
    public function model(): string
    {
        return Designation::class;
    }

    public function list($filters): Collection
    {
        $queryBuilder = $this->model::query()->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("designations.company_id"), $value);
            })
            ->when($filters->search ?? false, function($builder, $value){
                $builder->where(function($clause) use($value){
                    $clause->where('designations.name', 'LIKE', ('%' . $value . '%'));
                });
            })
            ->select([
                'designations.id AS id',
                'designations.company_id AS company_id',
                'designations.name AS name'
            ])
            ->orderBy('name', 'ASC');

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }

    public function selection($filters): Collection
    {
        $queryBuilder = $this->model::query()->getQuery()
            ->when(property_exists($filters, 'company_id'), function ($builder) use($filters){
                $builder->where('designations.company_id', $filters->company_id ?? null);
            })
            ->select([
                'designations.id AS id',
                'designations.name AS name',
            ])
            ->orderBy('name', 'ASC');

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }
}
