<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Facades\Fractal;
use App\Models\EmploymentProfile;
use App\Transformers\EmploymentProfile\PatchableTransformer;

class EmploymentProfileRepositoryEloquent extends BaseRepositoryEloquent implements EmploymentProfileRepository
{
    public function model(): string
    {
        return EmploymentProfile::class;
    }

    public function list($filters)
    {
        $queryBuilder = $this->model->getQuery()
            ->when(!empty($filters->employee_id) && is_array($filters->employee_id), function ($builder) use ($filters) {
                $builder->whereIn('employment_profiles.employee_id', $filters->employee_id);
            })
            ->select([
                'employment_profiles.*',
            ])
            ->orderBy('employment_profiles.start_date', 'ASC');

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }

    public function store($attributes)
    {
        $hydrated = $this->hydrateItem($attributes);

        $patchable = Fractal::item($hydrated, PatchableTransformer::class);

        return $this->model::create($patchable);
    }

    public function update($id, $attributes)
    {
        $model = $this->model::findOrfail($id);

        $hydrated = $this->hydrateItem($attributes);

        $patchable = Fractal::item($hydrated, PatchableTransformer::class);

        $model->update($patchable);

        return $model;
    }
}

