<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\EmploymentStatus;
use App\Models\Employee;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class EmployeeRepositoryEloquent extends BaseRepositoryEloquent implements EmployeeRepository
{
    public function model(): string
    {
        return Employee::class;
    }

    public function list($filters)
    {
        $currentEmploymentProfile = App::make(EmploymentProfileRepository::class)->currentEmploymentProfileBuilder($filters);

        $queryBuilder = $this->model::getQuery()
            ->leftJoin('companies', 'companies.id', '=', 'employees.company_id')
            ->leftJoinSub($currentEmploymentProfile, 'current_employment_profile', function ($join) {
                $join->on('employees.id', '=', 'current_employment_profile.employee_id')
                    ->where('current_employment_profile.row_number', 1);
            })
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("employees.company_id"), $value);
            })
            ->when(!empty($filters->employment_status) && is_array($filters->employment_status), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("COALESCE(current_employment_profile.status, " . EmploymentStatus::INACTIVE->value . ")"), $filters->employment_status);
            })
            ->when($filters->search ?? false, function($builder, $value){
                $builder->where(function($clause) use($value){
                    $clause->where('employees.number', 'LIKE', "%$value%")
                        ->orWhere('employees.family_name', 'LIKE', "%$value%")
                    ->orWhere('employees.given_name', 'LIKE', "%$value%");
                });
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(ORDER BY employees.family_name, employees.given_name) AS `row_number`"),
                DB::raw("DATE(CONVERT_TZ(UTC_TIMESTAMP(), 'UTC', companies.timezone)) AS local_date"),
                "employees.*",
                DB::raw("IF(current_employment_profile.id IS NULL, 0, 1) AS employment_status_active"),
                DB::raw("current_employment_profile.id AS employment_profile_id"),
                DB::raw("current_employment_profile.employee_id AS employment_profile_employee_id"),
                DB::raw("COALESCE(current_employment_profile.status, " . EmploymentStatus::INACTIVE->value . ") AS current_employment_status"),
                DB::raw("current_employment_profile.employment_type AS current_employment_type"),
                DB::raw("current_employment_profile.start_date AS current_employment_start_date"),
                DB::raw("current_employment_profile.end_of_service_type AS current_employment_end_of_service_type"),
                DB::raw("current_employment_profile.end_date AS current_employment_end_date"),
            ])
            ->orderBy("employees.family_name", 'ASC')
            ->orderBy("employees.given_name", 'ASC');

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, new $this->model);
    }

    public function selection($filters)
    {
        $queryBuilder = $this->model::getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("employees.company_id"), $value);
            })
            ->when(!empty($filters->id) && is_array($filters->id), function ($builder) use ($filters) {
                $builder->whereIn('employees.id', $filters->id);
            })
            ->when(!empty($filters->except_id) && is_array($filters->except_id), function ($builder) use ($filters) {
                $builder->whereNotIn('employees.id', $filters->except_id);
            })
            ->when($filters->search ?? false, function($builder, $value){
                $builder->whereRaw("CONCAT_WS(' ', family_name, middle_name, given_name) LIKE ?", ["%{$value}%"]);
            })
            ->select([
                "employees.*"
            ])
            ->orderBy("employees.family_name", 'ASC')
            ->orderBy("employees.middle_name", 'ASC')
            ->orderBy("employees.given_name", 'ASC');

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, new $this->model);
    }

    public function show($ulid)
    {
        $queryBuilder = $this->model::where('ulid', $ulid);

        return $queryBuilder->firstOrFail();
    }
}
