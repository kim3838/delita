<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\EmploymentStatus;
use App\Facades\Fractal;
use App\Models\EmploymentProfile;
use App\Transformers\EmploymentProfile\PatchableTransformer;
use Illuminate\Support\Facades\DB;

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

    public function baseQueryBuilder($filters)
    {
        return $this->model->getQuery()
            ->leftJoin('employees', 'employees.id', '=', 'employment_profiles.employee_id')
            ->leftJoin('companies', 'companies.id', '=', 'employees.company_id')
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("employees.company_id"), $value);
            })
            ->when(!empty($filters->status) && is_array($filters->status), function ($builder) use ($filters) {
                $builder->whereIn('employment_profiles.status', $filters->status);
            });
    }

    public function currentEmploymentProfileBuilder($filters)
    {
        $filters = (object)[
            'status' => [EmploymentStatus::ACTIVE],
            'company_id' => $filters->company_id ?? false,
        ];

        $innerQueryBuilder = $this->baseQueryBuilder($filters)
            ->select([
                DB::raw("DATE(CONVERT_TZ(UTC_TIMESTAMP(), 'UTC', companies.timezone)) AS local_date"),
                'employment_profiles.*'
            ]);

        $queryBuilder = $this->subQuery($innerQueryBuilder, 'employment_profiles_subquery')
            ->where('employment_profiles_subquery.start_date', '<=', DB::raw("employment_profiles_subquery.local_date"))
            ->where(function ($query) {
                $query->whereNull('employment_profiles_subquery.end_date')
                    ->orWhere('employment_profiles_subquery.end_date', '>=', DB::raw("employment_profiles_subquery.local_date"));
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(PARTITION BY employee_id ORDER BY start_date DESC, created_at DESC) AS `row_number`"),
                'employment_profiles_subquery.*'
            ]);

        return $queryBuilder;
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

