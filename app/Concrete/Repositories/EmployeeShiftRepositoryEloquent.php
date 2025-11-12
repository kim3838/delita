<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\EmployeeShiftRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Hydrations\Employee\ShiftAssignment;
use App\Models\Hydrations\Employee\ShiftsByEmployees;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class EmployeeShiftRepositoryEloquent extends BaseRepositoryEloquent implements EmployeeShiftRepository
{
    public function model(): string
    {
        return EmployeeShift::class;
    }

    public function baseQueryBuilder($filters, $orders = null)
    {
        $employeeRepositoryFilter = clone $filters;
        unset($employeeRepositoryFilter->assigned_shift_ids);
        unset($employeeRepositoryFilter->not_assigned_shift_ids);

        $employeeQueryBuilder = App::make(EmployeeRepository::class)->baseQueryBuilder($employeeRepositoryFilter, []);

        $queryBuilder = $this->model::getQuery()
            ->joinSub($employeeQueryBuilder, 'employee_sub', function ($join) {
                $join->on('employee_sub.id', '=', 'employee_shift.employee_id');
            })
            ->join('shifts', 'shifts.id', '=', 'employee_shift.shift_id')
            ->when(!empty($filters->assigned_shift_ids) && is_array($filters->assigned_shift_ids), function ($builder) use ($filters) {
                $builder->whereIn('employee_shift.shift_id', $filters->assigned_shift_ids);
            })
            ->when(!empty($filters->not_assigned_shift_ids) && is_array($filters->not_assigned_shift_ids), function ($builder) use ($filters) {
                $builder->whereNotIn('employee_shift.shift_id', $filters->not_assigned_shift_ids);
            });

        return $queryBuilder;
    }

    public function list($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'employee_sub.family_name', 'direction' => 'ASC'],
            ['field' => 'employee_sub.given_name', 'direction' => 'ASC'],
            ['field' => 'shifts.code', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters)
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                'employee_shift.id AS id',
                'employee_shift.id AS employee_shift_id',
                'employee_shift.employee_id AS employee_id',
                'employee_shift.shift_id AS shift_id',

                'employee_sub.number AS employee_number',
                'employee_sub.employment_status_active AS employee_employment_status_active',
                'employee_sub.current_employment_status AS employee_current_employment_status',
                'employee_sub.current_employment_type AS employee_current_employment_type',

                'shifts.ulid AS shift_ulid',
                'shifts.code AS shift_code',
                'shifts.name AS shift_name',

                'employee_shift.start_date AS shift_start_date',
                'employee_shift.stated_shift_end_date AS shift_stated_shift_end_date',
                'employee_shift.end_date AS shift_end_date',
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, new ShiftAssignment());
    }

    public function selection($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'shifts.code', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->model->getQuery()
            ->join('shifts', 'shifts.id', '=', 'employee_shift.shift_id')
            ->when($filters->employee_id ?? false, function ($builder, $value) {
                $builder->where('employee_shift.employee_id', $value);
            })
            ->when($filters->search ?? false, function ($builder, $value) {
                $builder->where(function ($query) use ($value) {
                    $query->where('shifts.code', 'like', "%$value%")
                        ->orWhere('shifts.name', 'like', "%$value%");
                });
            })
            ->select([
                'shifts.id AS shift_id',
                'shifts.ulid AS shift_ulid',
                'shifts.code AS shift_code',
                'shifts.name AS shift_name',

                'employee_shift.start_date AS shift_start_date',
                'employee_shift.stated_shift_end_date AS shift_stated_shift_end_date',
                'employee_shift.end_date AS shift_end_date',
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, new ShiftAssignment());
    }

    public function shiftsByEmployees($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'employee_sub.family_name', 'direction' => 'ASC'],
            ['field' => 'employee_sub.given_name', 'direction' => 'ASC'],
        ];

        $groups = [
            'employee_sub.id'
        ];

        $employeeQueryBuilder = App::make(EmployeeRepository::class)->baseQueryBuilder($filters, []);

        $queryBuilder = app(Builder::class)->fromSub($employeeQueryBuilder, 'employee_sub')
            ->leftJoin('employee_shift', 'employee_shift.employee_id', '=', 'employee_sub.id')
            ->leftJoin('shifts', 'shifts.id', '=', 'employee_shift.shift_id')
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                'employee_sub.id AS employee_id',
                'employee_sub.number AS employee_number',
                DB::raw("MAX(employee_sub.employment_status_active) AS employee_employment_status_active"),
                DB::raw("MAX(employee_sub.current_employment_status) AS employee_current_employment_status"),
                DB::raw("MAX(employee_sub.current_employment_type) AS employee_current_employment_type"),
                DB::raw("GROUP_CONCAT(shifts.code) AS assigned_shift_codes"),
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);
        $this->setGroupsOnBuilder($queryBuilder, $groups);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, new ShiftsByEmployees());
    }

    public function syncWithoutDetaching($employeeIds, $shiftIds, $pivotData): void
    {
        foreach ($employeeIds as $employeeId) {

            $employee = Employee::query()->find($employeeId);

            $sync = collect($shiftIds)->mapWithKeys(fn ($id) => [$id => $pivotData])->toArray();

            $employee->shifts()->syncWithoutDetaching($sync);
        }
    }

    public function detachAssignedShifts($selectedMorphables, $morphMapKey): void
    {
        $related = match($morphMapKey){'employee' => 'shifts','shift' => 'employees'};

        foreach ($selectedMorphables as $selectedMorphable) {

            Relation::getMorphedModel($morphMapKey)::query()
                ->find($selectedMorphable)
                ->{$related}()
                ->detach();
        }
    }
}
