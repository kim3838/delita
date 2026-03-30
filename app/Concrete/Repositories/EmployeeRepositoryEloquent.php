<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\DepartmentRepository;
use App\Blueprint\Repositories\EmployeePayrollComponentRepository;
use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\EmployeeShiftRepository;
use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Blueprint\Repositories\PayFrequencyRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\DepartmentEmployeeAssignmentType;
use App\Enums\EmploymentStatus;
use App\Enums\Formulable;
use App\Enums\PayFrequency;
use App\Enums\PayPeriod;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class EmployeeRepositoryEloquent extends BaseRepositoryEloquent implements EmployeeRepository
{
    protected array $defaultOrders = [
        ['field' => 'employees.number', 'direction' => 'ASC'],
    ];

    public function model(): string
    {
        return Employee::class;
    }

    public function baseQueryBuilder($filters, $orders = [], $relations = []): QueryBuilder
    {
        $withCurrentEmploymentProfile = in_array('current_employment_profile', $relations);

        $queryBuilder = $this->model::query()->getQuery()
            ->leftJoin('companies', 'companies.id', '=', 'employees.company_id')
            ->when(in_array('user', $relations), function ($builder) {
                $builder->leftJoin('users', 'users.id', '=', 'employees.user_id');
            })
            ->when(in_array('department', $relations), function ($builder) {
                $builder->leftJoin('department_employee', 'department_employee.employee_id', '=', 'employees.id')
                    ->leftJoin('departments', 'departments.id', '=', 'department_employee.department_id');
            })
            ->when(in_array('designation', $relations), function ($builder) {
                $builder->leftJoin('designations', 'designations.id', '=', 'employees.designation_id');
            })
            ->when($withCurrentEmploymentProfile, function ($builder) use($filters) {

                $currentEmploymentProfile = App::make(EmploymentProfileRepository::class)->currentEmploymentProfileBuilder($filters);

                $builder->leftJoinSub($currentEmploymentProfile, 'current_employment_profile', function ($join) {
                    $join->on('employees.id', '=', 'current_employment_profile.employee_id')
                        ->where('current_employment_profile.row_number', 1);
                });
            })
            ->when(in_array('current_shift', $relations), function ($builder) use($filters) {
                $currentShiftFilters = $filters;
                $currentShift = App::make(EmployeeShiftRepository::class)->currentShiftBuilder($currentShiftFilters);

                $builder->leftJoinSub($currentShift, 'current_shift_sub', function ($join) {
                    $join->on('employees.id', '=', 'current_shift_sub.employee_id');
                });
            })
            ->when(in_array('upcoming_shift', $relations), function ($builder) use($filters) {
                $upcomingShiftFilters = $filters;
                $upcomingShift = App::make(EmployeeShiftRepository::class)->upcomingShiftBuilder($upcomingShiftFilters);

                $builder->leftJoinSub($upcomingShift, 'upcoming_shift_sub', function ($join) {
                    $join->on('employees.id', '=', 'upcoming_shift_sub.employee_id');
                });
            })
            ->when(property_exists($filters, 'company_id'), function ($builder) use($filters){
                $builder->where('employees.company_id', $filters->company_id ?? null);
            })
            ->when(!empty($filters->employee_ids) && is_array($filters->employee_ids), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("employees.id"), $filters->employee_ids);
            })
            ->when(!empty($filters->company_ids) && is_array($filters->company_ids), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("employees.company_id"), $filters->company_ids);
            })
            ->when(!empty($filters->employee_ulids) && is_array($filters->employee_ulids), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("employees.ulid"), $filters->employee_ulids);
            })
            ->when($withCurrentEmploymentProfile && !empty($filters->employment_status) && is_array($filters->employment_status), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("COALESCE(current_employment_profile.status, " . EmploymentStatus::INACTIVE->value . ")"), $filters->employment_status);
            })
            ->when($withCurrentEmploymentProfile && !empty($filters->employment_type) && is_array($filters->employment_type), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("current_employment_profile.employment_type"), $filters->employment_type);
            })
            ->when(!empty($filters->pay_frequency_ids) && is_array($filters->pay_frequency_ids), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("employees.pay_frequency_id"), $filters->pay_frequency_ids);
            })
            ->when(!empty($filters->designation_ids) && is_array($filters->designation_ids), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("employees.designation_id"), $filters->designation_ids);
            })
            ->when(isset($filters->has_user_account) && is_bool($filters->has_user_account) && $filters->has_user_account, function ($builder) use ($filters) {
                $builder->whereExists(function ($query) use ($filters) {
                    $query->select(DB::raw(1))
                        ->from('users')
                        ->whereColumn('employees.user_id', 'users.id');
                });
            })
            ->when(!empty($filters->department_ids) && is_array($filters->department_ids), function ($builder) use ($filters) {
                $builder->whereExists(function ($query) use ($filters) {
                    $query->select(DB::raw(1))
                        ->from('department_employee')
                        ->whereColumn('department_employee.employee_id', 'employees.id')
                        ->whereIn('department_employee.department_id', $filters->department_ids);
                });
            })
            ->when(!empty($filters->assigned_employee_group_ids) && is_array($filters->assigned_employee_group_ids), function ($builder) use ($filters) {
                $builder->whereExists(function ($query) use ($filters) {
                    $query->select(DB::raw(1))
                        ->from('groupables')
                        ->where('groupables.groupable_type', Relation::getMorphAlias($this->model()))
                        ->whereColumn('groupables.groupable_id', 'employees.id')
                        ->whereIn('groupables.group_id', $filters->assigned_employee_group_ids);
                });
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
            ->when(!empty($filters->assigned_leave_type_ids) && is_array($filters->assigned_leave_type_ids), function ($builder) use ($filters) {
                $builder->whereExists(function ($query) use ($filters) {
                    $query->select(DB::raw(1))
                        ->from('employee_leave_type')
                        ->whereColumn('employee_leave_type.employee_id', 'employees.id')
                        ->whereIn('employee_leave_type.leave_type_id', $filters->assigned_leave_type_ids);
                });
            })
            ->when(!empty($filters->not_assigned_leave_type_ids) && is_array($filters->not_assigned_leave_type_ids), function ($builder) use ($filters) {
                $builder->whereNotExists(function ($query) use ($filters) {
                    $query->select(DB::raw(1))
                        ->from('employee_leave_type')
                        ->whereColumn('employee_leave_type.employee_id', 'employees.id')
                        ->whereIn('employee_leave_type.leave_type_id', $filters->not_assigned_leave_type_ids);
                });
            })
            ->when($filters->search ?? false, function ($builder, $value) {
                $builder->where(function ($clause) use ($value) {
                    $clause->where('employees.number', 'LIKE', "%$value%")
                        ->orWhere('employees.family_name', 'LIKE', "%$value%")
                        ->orWhere('employees.given_name', 'LIKE', "%$value%")
                        ->orWhere(DB::raw("CONCAT(employees.family_name, ' ', employees.given_name)"), 'LIKE', "%$value%")
                        ->orWhere(DB::raw("CONCAT(employees.family_name, ' ', employees.given_name, ' ', employees.middle_name)"), 'LIKE', "%$value%");
                });
            })
            ->when(!empty($filters->or_employee_ids) && is_array($filters->or_employee_ids), function ($builder) use ($filters) {
                $builder->orWhereIn(DB::raw("employees.id"), $filters->or_employee_ids);
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(" . $this->rowNumberOrder($orders) . ") AS `row_number`"),
                DB::raw("DATE(CONVERT_TZ(UTC_TIMESTAMP(), 'UTC', companies.timezone)) AS local_date"),
                "companies.timezone AS company_timezone",
                "companies.currency AS company_currency_code",

                "employees.id",
                "employees.ulid",
                "employees.user_id",
                "employees.company_id",
                "employees.designation_id",
                "employees.manager_id",
                "employees.pay_frequency_id",
                "employees.number",
                DB::raw("CONCAT_WS(' ',family_name,given_name,middle_name) AS full_name"),
                "employees.birth_date",
                "employees.gender",
                "employees.marital_status",
                "employees.date_registered",
                "employees.creation_type",

                ...(in_array('user', $relations) ? [
                    DB::raw("users.id AS user_id"),
                    DB::raw("users.name AS user_name"),
                    DB::raw("users.email AS user_email"),
                    DB::raw("users.status AS user_status"),
                    DB::raw("users.email_verified_at AS user_email_verified_at"),
                    DB::raw("users.timezone AS user_timezone"),
                ] : []),

                ...(in_array('department', $relations) ? [
                    DB::raw("department_employee.id AS department_employee_id"),
                    DB::raw("department_employee.department_assignment_type AS department_assignment_type"),
                    DB::raw("department_employee.department_id AS department_id"),
                    DB::raw("departments.name AS department_name"),
                ] : []),

                ...(in_array('designation', $relations) ? [
                    DB::raw("designations.name AS designation_name"),
                ] : []),

                ...(in_array('current_employment_profile', $relations) ? [
                    DB::raw("CASE WHEN current_employment_profile.id IS NULL OR COALESCE(current_employment_profile.status, 200) = 200 THEN 0 ELSE 1 END AS employment_status_active"),
                    DB::raw("current_employment_profile.id AS employment_profile_id"),
                    DB::raw("current_employment_profile.employee_id AS employment_profile_employee_id"),
                    DB::raw("COALESCE(current_employment_profile.status, " . EmploymentStatus::INACTIVE->value . ") AS current_employment_status"),
                    DB::raw("current_employment_profile.employment_type AS current_employment_type"),
                    DB::raw("current_employment_profile.start_date AS current_employment_start_date"),
                    DB::raw("current_employment_profile.end_of_service_type AS current_employment_end_of_service_type"),
                    DB::raw("current_employment_profile.end_date AS current_employment_end_date"),
                ] : []),

                ...(in_array('current_shift', $relations) ? [
                    'current_shift_sub.employee_shift_id AS current_employee_shift_id',
                    'current_shift_sub.shift_id AS current_shift_id',
                    'current_shift_sub.shift_start_date AS current_shift_start_date',
                    'current_shift_sub.shift_stated_shift_end_date AS current_shift_stated_shift_end_date',
                    'current_shift_sub.shift_end_date AS current_shift_end_date',
                ] : []),

                ...(in_array('upcoming_shift', $relations) ? [
                    'upcoming_shift_sub.employee_shift_id AS upcoming_employee_shift_id',
                    'upcoming_shift_sub.shift_id AS upcoming_shift_id',
                    'upcoming_shift_sub.shift_start_date AS upcoming_shift_start_date',
                    'upcoming_shift_sub.shift_stated_shift_end_date AS upcoming_shift_stated_shift_end_date',
                    'upcoming_shift_sub.shift_end_date AS upcoming_shift_end_date',
                ] : []),
            ]);

        return $queryBuilder;
    }

    public function paginate($filters, $relations = []): LengthAwarePaginator
    {
        $queryBuilder = $this->baseQueryBuilder($filters, $this->defaultOrders, $relations);

        $this->setOrdersOnBuilder($queryBuilder, $this->defaultOrders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function list($filters, $relations = []): Collection
    {
        $queryBuilder = $this->baseQueryBuilder($filters, [], $relations);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }

    public function queryBuilderCursor($filters): LazyCollection
    {
        $queryBuilder = $this->baseQueryBuilder($filters, $this->defaultOrders);

        return $queryBuilder->cursor();
    }

    public function selection($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'employees.number', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->model::query()->getQuery()
            ->when(property_exists($filters, 'company_id'), function ($builder) use($filters){
                $builder->where('employees.company_id', $filters->company_id ?? null);
            })
            ->when(!empty($filters->id) && !is_array($filters->id), function ($builder) use($filters){
                $builder->where('employees.id', $filters->id);
            })
            ->when(!empty($filters->id) && is_array($filters->id), function ($builder) use ($filters) {
                $builder->whereIn('employees.id', $filters->id);
            })
            ->when(!empty($filters->except_ids) && is_array($filters->except_ids), function ($builder) use ($filters) {
                $builder->whereNotIn('employees.id', $filters->except_ids);
            })
            ->when($filters->search ?? false, function($builder, $value){
                $builder->whereRaw("CONCAT_WS(' ', family_name, middle_name, given_name) LIKE ?", ["%{$value}%"])
                    ->orWhere('employees.number', 'LIKE', "%$value%");
            })
            ->select([
                "employees.*",
                DB::raw("CONCAT_WS(' ',employees.family_name,employees.given_name,employees.middle_name) AS full_name"),
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function show($identifier)
    {
        $queryBuilder = $this->model::query()->where('ulid', $identifier);

        return $queryBuilder->firstOrFail();
    }

    public function store($attributes)
    {
        $model = $this->model::query()->create($attributes);

        $this->syncDepartments($model, $attributes);

        return $model;
    }

    public function update($identifier, $attributes)
    {
        $model = $this->model::query()->findOrfail($identifier);

        $this->syncDepartments($model, $attributes);

        $model->update($attributes);

        return $model;
    }

    public function batchUpdate($employeeIdentifiers, $attributes): array
    {
        $errors = [];

        foreach($employeeIdentifiers as $identifier){

            $employee = $this->model::query()->findOrfail($identifier);

            if(!$attributes['keep_department'] && $attributes['department_assignment_type'] == DepartmentEmployeeAssignmentType::HEAD->value){

                $departmentHead = App::make(DepartmentRepository::class)->model()::query()
                    ->find($attributes['department_id'])->employees()
                    ->wherePivot('department_assignment_type', DepartmentEmployeeAssignmentType::HEAD->value)->first();

                if(!empty($departmentHead)){
                    $attributes['department_assignment_type'] = DepartmentEmployeeAssignmentType::DEFAULT->value;
                }
            }

            if(!$attributes['keep_department']){

                $this->syncDepartments($employee, [
                    'department_id' => $attributes['department_id'] ?? null,
                    'department_assignment_type' => $attributes['department_assignment_type']
                ]);
            }

            $updateAttributes = [

                ...(!$attributes['keep_designation'] ? [
                    'designation_id' => $attributes['designation_id'] ?? null,
                ] : []),

                ...((!$attributes['keep_manager'] && $identifier!== $attributes['manager_id']) ? [
                    'manager_id' => $attributes['manager_id'] ?? null,
                ] : []),

                ...($attributes['unlink_user_account'] ? [
                    'user_id' => null,
                ] : [])
            ];

            $employee->update($updateAttributes);

            if(!$attributes['keep_pay_frequency']){

                $newPayFrequency = $attributes['pay_frequency_id'] ?? null;
                $removePayFrequency = empty($attributes['pay_frequency_id']);
                $employeeAmountableCompensationsAreValidWithNewPayFrequency = true;

                if(!empty($newPayFrequency)){

                    $payFrequency = App::make(PayFrequencyRepository::class)->model()::query()->find($newPayFrequency);

                    $payFrequencyIsWeekly = $payFrequency?->type == PayFrequency::WEEKLY;

                    if($payFrequencyIsWeekly){

                        $employeeAmountableSemiMonthlyOrMonthlyCompensation = App::make(EmployeePayrollComponentRepository::class)->model()::query()
                            ->where('employee_id', $employee->id)
                            ->where('payroll_componentable_type', 'compensation')
                            ->where('formulable_type', Formulable::EARNINGS->value)
                            ->whereIn('pay_period', [PayPeriod::SEMIMONTHLY->value, PayPeriod::MONTHLY->value])
                            ->get()->toArray();

                        if(!empty($employeeAmountableSemiMonthlyOrMonthlyCompensation)){

                            $employeeAmountableCompensationsAreValidWithNewPayFrequency = false;
                            $errors[] = [
                                'employee_number' => $employee->number,
                                'employee_full_name' => $employee->full_name_attribute,
                                'error' => 'Unable to apply weekly payroll group if employee has semi-monthly or monthly compensation amount pay period.'
                            ];
                        }
                    }
                }

                if($removePayFrequency || $employeeAmountableCompensationsAreValidWithNewPayFrequency){
                    $employee->update(['pay_frequency_id' => $newPayFrequency]);
                }
            }
        }

        return $errors;
    }

    public function syncDepartments($model, $attributes): void
    {
        if($attributes['department_id'] && in_array($attributes['department_assignment_type'], DepartmentEmployeeAssignmentType::valuesToArray())){

            $sync = collect([$attributes['department_id']])->mapWithKeys(fn ($id) => [$id => ['department_assignment_type' => $attributes['department_assignment_type']]])->toArray();

            $model->departments()->sync($sync);

        } else {

            $model->departments()->detach();
        }
    }
}
