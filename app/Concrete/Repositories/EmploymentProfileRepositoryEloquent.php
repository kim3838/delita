<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\EmploymentStatus;
use App\Facades\Fractal;
use App\Models\EmploymentProfile;
use App\Transformers\EmploymentProfile\PatchableTransformer;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class EmploymentProfileRepositoryEloquent extends BaseRepositoryEloquent implements EmploymentProfileRepository
{
    public function model(): string
    {
        return EmploymentProfile::class;
    }

    public function baseQueryBuilder($filters, $orders = [], $relations = []): QueryBuilder
    {
        $employeeRepositoryFilter = clone $filters;
        unset($employeeRepositoryFilter->employment_profile_status);

        $employeeQueryBuilder = App::make(EmployeeRepository::class)->baseQueryBuilder($employeeRepositoryFilter, []);

        return $this->model::query()->getQuery()
            ->joinSub($employeeQueryBuilder, 'employee_sub', function ($join) {
                $join->on('employee_sub.id', '=', 'employment_profiles.employee_id');
            })
            ->when(!empty($filters->employment_profile_status) && is_array($filters->employment_profile_status), function ($builder) use ($filters) {
                $builder->whereIn('employment_profiles.status', $filters->employment_profile_status);
            })
            ->select([
                'employee_sub.company_timezone AS company_timezone',
                'employee_sub.local_date AS local_date',
                'employee_sub.number AS employee_number',
                'employee_sub.full_name AS employee_full_name',
                "employment_profiles.*",
            ]);
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'base_employment_profile_sub.employee_number', 'direction' => 'ASC'],
            ['field' => 'base_employment_profile_sub.start_date', 'direction' => 'ASC'],
            ['field' => 'base_employment_profile_sub.created_at', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->queryAsSub($this->baseQueryBuilder($filters, $orders), 'base_employment_profile_sub')
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                'base_employment_profile_sub.*'
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function list($filters): Collection
    {
        $orders = [
            ['field' => 'employment_profiles.start_date', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }

    public function currentEmploymentProfileBuilder($filters): QueryBuilder
    {
        $filters = (object)[
            'employment_profile_status' => [EmploymentStatus::ACTIVE],
            'company_id' => $filters->company_id ?? false,
        ];

        $innerQueryBuilder = $this->queryAsSub($this->baseQueryBuilder($filters), 'base_employment_profile_sub')
            ->select([
                'base_employment_profile_sub.local_date AS local_date',

                'base_employment_profile_sub.id AS id',
                'base_employment_profile_sub.employee_id AS employee_id',
                'base_employment_profile_sub.status AS status',
                'base_employment_profile_sub.employment_type AS employment_type',
                'base_employment_profile_sub.start_date AS start_date',
                'base_employment_profile_sub.end_of_service_type AS end_of_service_type',
                'base_employment_profile_sub.end_date AS end_date',
                'base_employment_profile_sub.created_at AS created_at',
                'base_employment_profile_sub.updated_at AS updated_at'
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

        return $this->model::query()->create($patchable);
    }

    public function update($identifier, $attributes)
    {
        $model = $this->model::query()->findOrfail($identifier);

        $hydrated = $this->hydrateItem($attributes);

        $patchable = Fractal::item($hydrated, PatchableTransformer::class);

        $model->update($patchable);

        return $model;
    }
}

