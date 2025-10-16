<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\EmploymentStatus;
use App\Models\Employee;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class EmployeeRepositoryEloquent extends BaseRepositoryEloquent implements EmployeeRepository
{
    protected array $defaultOrders = [
        ['field' => 'employees.family_name', 'direction' => 'ASC'],
        ['field' => 'employees.given_name', 'direction' => 'ASC'],
    ];

    public function model(): string
    {
        return Employee::class;
    }

    public function baseQueryBuilder($filters, $orders = null)
    {

        $orders = $orders ?? $this->defaultOrders;

        $currentEmploymentProfile = App::make(EmploymentProfileRepository::class)
            ->currentEmploymentProfileBuilder($filters);

        $queryBuilder = $this->model::getQuery()
            ->leftJoin('users', 'users.id', '=', 'employees.user_id')
            ->leftJoin('companies', 'companies.id', '=', 'employees.company_id')
            ->leftJoinSub($currentEmploymentProfile, 'current_employment_profile', function ($join) {
                $join->on('employees.id', '=', 'current_employment_profile.employee_id')
                    ->where('current_employment_profile.row_number', 1);
            })
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("employees.company_id"), $value);
            })
            ->when(!empty($filters->employee_ids) && is_array($filters->employee_ids), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("employees.id"), $filters->employee_ids);
            })
            ->when(!empty($filters->employment_status) && is_array($filters->employment_status), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("COALESCE(current_employment_profile.status, " . EmploymentStatus::INACTIVE->value . ")"), $filters->employment_status);
            })
            ->when(!empty($filters->employment_type) && is_array($filters->employment_type), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("current_employment_profile.employment_type"), $filters->employment_type);
            })
            ->when(!empty($filters->department_ids) && is_array($filters->department_ids), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("employees.department_id"), $filters->department_ids);
            })
            ->when(!empty($filters->designation_ids) && is_array($filters->designation_ids), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("employees.designation_id"), $filters->designation_ids);
            })
            ->when(!empty($filters->assigned_shift_ids) && is_array($filters->assigned_shift_ids), function ($builder) use ($filters) {
                $builder->whereExists(function ($query) use ($filters) {
                    $query->select(DB::raw(1))
                        ->from('employee_shift')
                        ->whereColumn('employee_shift.employee_id', 'employees.id')
                        ->whereIn('employee_shift.shift_id', $filters->assigned_shift_ids);
                });
            })
            ->when(!empty($filters->not_assigned_shift_ids) && is_array($filters->not_assigned_shift_ids), function ($builder) use ($filters) {
                $builder->whereNotExists(function ($query) use ($filters) {
                    $query->select(DB::raw(1))
                        ->from('employee_shift')
                        ->whereColumn('employee_shift.employee_id', 'employees.id')
                        ->whereIn('employee_shift.shift_id', $filters->not_assigned_shift_ids);
                });
            })
            ->when($filters->search ?? false, function($builder, $value){
                $builder->where(function($clause) use($value){
                    $clause->where('employees.number', 'LIKE', "%$value%")
                        ->orWhere('employees.family_name', 'LIKE', "%$value%")
                        ->orWhere('employees.given_name', 'LIKE', "%$value%");
                });
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(" . $this->rowNumberOrder($orders) . ") AS `row_number`"),
                DB::raw("DATE(CONVERT_TZ(UTC_TIMESTAMP(), 'UTC', companies.timezone)) AS local_date"),
                "employees.*",
                DB::raw("users.name AS user_name"),
                DB::raw("users.email AS user_email"),
                DB::raw("users.email_verified_at AS user_email_verified_at"),
                DB::raw("users.status AS user_status"),
                DB::raw("IF(current_employment_profile.id IS NULL, 0, 1) AS employment_status_active"),
                DB::raw("current_employment_profile.id AS employment_profile_id"),
                DB::raw("current_employment_profile.employee_id AS employment_profile_employee_id"),
                DB::raw("COALESCE(current_employment_profile.status, " . EmploymentStatus::INACTIVE->value . ") AS current_employment_status"),
                DB::raw("current_employment_profile.employment_type AS current_employment_type"),
                DB::raw("current_employment_profile.start_date AS current_employment_start_date"),
                DB::raw("current_employment_profile.end_of_service_type AS current_employment_end_of_service_type"),
                DB::raw("current_employment_profile.end_date AS current_employment_end_date"),
            ]);

        return $queryBuilder;
    }

    public function list($filters): LengthAwarePaginator
    {
        $queryBuilder = $this->baseQueryBuilder($filters);

        $this->setOrdersOnBuilder($queryBuilder, $this->defaultOrders);

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
