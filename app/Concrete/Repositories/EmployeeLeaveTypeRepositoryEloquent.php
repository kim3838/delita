<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeeLeaveTypeRepository;
use App\Blueprint\Repositories\EmployeeRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Employee;
use App\Models\EmployeeLeaveType;
use App\Models\Hydrations\Employee\LeaveTypeAssignment;
use App\Models\Hydrations\Employee\LeaveTypesByEmployees;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class EmployeeLeaveTypeRepositoryEloquent extends BaseRepositoryEloquent implements EmployeeLeaveTypeRepository
{
    public function model(): string
    {
        return EmployeeLeaveType::class;
    }

    public function baseQueryBuilder($filters, $orders = [])
    {
        $employeeRepositoryFilter = clone $filters;
        unset($employeeRepositoryFilter->assigned_leave_type_ids);
        unset($employeeRepositoryFilter->not_assigned_leave_type_ids);

        $employeeQueryBuilder = App::make(EmployeeRepository::class)->baseQueryBuilder($employeeRepositoryFilter, [], ['current_employment_profile']);

        $queryBuilder = $this->model::query()->getQuery()
            ->joinSub($employeeQueryBuilder, 'employee_sub', function ($join) {
                $join->on('employee_sub.id', '=', 'employee_leave_type.employee_id');
            })
            ->join('leave_types', 'leave_types.id', '=', 'employee_leave_type.leave_type_id')
            ->when(!empty($filters->assigned_leave_type_ids) && is_array($filters->assigned_leave_type_ids), function ($builder) use ($filters) {
                $builder->whereIn('employee_leave_type.leave_type_id', $filters->assigned_leave_type_ids);
            })
            ->when(!empty($filters->not_assigned_leave_type_ids) && is_array($filters->not_assigned_leave_type_ids), function ($builder) use ($filters) {
                $builder->whereNotIn('employee_leave_type.leave_type_id', $filters->not_assigned_leave_type_ids);
            });

        return $queryBuilder;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'employee_sub.number', 'direction' => 'ASC'],
            ['field' => 'leave_types.code', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters)
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                'employee_leave_type.id AS id',
                'employee_leave_type.id AS employee_leave_type_id',
                'employee_leave_type.employee_id AS employee_id',
                'employee_leave_type.leave_type_id AS leave_type_id',

                'employee_sub.number AS employee_number',
                'employee_sub.full_name AS employee_full_name',
                'employee_sub.employment_status_active AS employee_employment_status_active',
                'employee_sub.current_employment_status AS employee_current_employment_status',
                'employee_sub.current_employment_type AS employee_current_employment_type',

                'leave_types.ulid AS leave_type_ulid',
                'leave_types.code AS leave_type_code',
                'leave_types.name AS leave_type_name',
                'leave_types.initial_balance_upon_eligibility AS leave_type_initial_balance_upon_eligibility',

                'employee_leave_type.override_balance_upon_eligibility AS leave_type_assignment_override_balance_upon_eligibility',
                'employee_leave_type.balance_upon_eligibility AS leave_type_assignment_balance_upon_eligibility',
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, LeaveTypeAssignment::class);
    }

    public function selection($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'leave_types.code', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->model::query()->getQuery()
            ->join('leave_types', 'leave_types.id', '=', 'employee_leave_type.leave_type_id')
            ->when($filters->employee_id ?? false, function ($builder, $value) {
                $builder->where('employee_leave_type.employee_id', $value);
            })
            ->when($filters->search ?? false, function ($builder, $value) {
                $builder->where(function ($query) use ($value) {
                    $query->where('leave_types.code', 'like', "%$value%")
                        ->orWhere('leave_types.name', 'like', "%$value%");
                });
            })
            ->select([
                'leave_types.id AS leave_type_id',
                'leave_types.ulid AS leave_type_ulid',
                'leave_types.code AS leave_type_code',
                'leave_types.name AS leave_type_name',
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, LeaveTypeAssignment::class);
    }

    public function leaveTypesByEmployees($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'employee_sub.number', 'direction' => 'ASC'],
            ['field' => 'employee_sub.full_name', 'direction' => 'ASC'],
        ];

        $groups = [
            'employee_sub.id'
        ];

        $employeeQueryBuilder = App::make(EmployeeRepository::class)->baseQueryBuilder($filters, [], ['current_employment_profile']);

        $queryBuilder = app(Builder::class)->fromSub($employeeQueryBuilder, 'employee_sub')
            ->leftJoin('employee_leave_type', 'employee_leave_type.employee_id', '=', 'employee_sub.id')
            ->leftJoin('leave_types', 'leave_types.id', '=', 'employee_leave_type.leave_type_id')
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                'employee_sub.id AS employee_id',
                'employee_sub.number AS employee_number',
                'employee_sub.full_name AS employee_full_name',
                DB::raw("MAX(employee_sub.employment_status_active) AS employee_employment_status_active"),
                DB::raw("MAX(employee_sub.current_employment_status) AS employee_current_employment_status"),
                DB::raw("MAX(employee_sub.current_employment_type) AS employee_current_employment_type"),
                DB::raw("GROUP_CONCAT(leave_types.code ORDER BY leave_types.code ASC) AS assigned_leave_type_codes"),
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);
        $this->setGroupsOnBuilder($queryBuilder, $groups);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, LeaveTypesByEmployees::class);
    }

    public function syncWithoutDetaching($employeeIds, $leaveTypeIds, $pivotData): void
    {
        foreach ($employeeIds as $employeeId) {

            $employee = Employee::query()->find($employeeId);

            $sync = collect($leaveTypeIds)->mapWithKeys(fn ($id) => [$id => $pivotData])->toArray();

            $employee->leaveTypes()->syncWithoutDetaching($sync);
        }
    }

    public function detachAssignedLeaveTypes($selectedMorphables, $morphMapKey): void
    {
        $related = match($morphMapKey){'employee' => 'leaveTypes','leave_type' => 'employees'};

        foreach ($selectedMorphables as $selectedMorphable) {

            Relation::getMorphedModel($morphMapKey)::query()
                ->find($selectedMorphable)
                ->{$related}()
                ->detach();
        }
    }
}
