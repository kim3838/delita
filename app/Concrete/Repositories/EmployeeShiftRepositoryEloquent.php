<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\EmployeeShiftRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Hydrations\Employee\ShiftAssignment;
use App\Models\Hydrations\Employee\ShiftsByEmployees;
use Carbon\Carbon;
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

    public function baseQueryBuilder($filters, $orders = [])
    {
        $employeeRepositoryFilter = clone $filters;
        unset($employeeRepositoryFilter->assigned_shift_ids);
        unset($employeeRepositoryFilter->not_assigned_shift_ids);

        $employeeQueryBuilder = App::make(EmployeeRepository::class)->baseQueryBuilder($employeeRepositoryFilter, [], ['current_employment_profile']);

        $queryBuilder = $this->model::query()->getQuery()
            ->joinSub($employeeQueryBuilder, 'employee_sub', function ($join) {
                $join->on('employee_sub.id', '=', 'employee_shift.employee_id');
            })
            ->join('shifts', 'shifts.id', '=', 'employee_shift.shift_id')
            ->select([
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
                DB::raw("
                    IF(ROW_NUMBER() OVER (
                    PARTITION BY employee_shift.employee_id
                    ORDER BY employee_shift.start_date DESC,
                    employee_shift.created_at DESC
                ) = 1, 1, 0) AS shift_is_latest"),
                'employee_shift.start_date AS shift_start_date',
                'employee_shift.stated_shift_end_date AS shift_stated_shift_end_date',
                'employee_shift.end_date AS shift_end_date',
            ]);

        $queryBuilder = $this->queryAsSub($queryBuilder, 'employee_shift_sub')
            ->when(!empty($filters->assigned_shift_ids) && is_array($filters->assigned_shift_ids), function ($builder) use ($filters) {
                $builder->whereIn('employee_shift_sub.shift_id', $filters->assigned_shift_ids);
            })
            ->when(!empty($filters->not_assigned_shift_ids) && is_array($filters->not_assigned_shift_ids), function ($builder) use ($filters) {
                $builder->whereNotIn('employee_shift_sub.shift_id', $filters->not_assigned_shift_ids);
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                'employee_shift_sub.*',
            ]);

        return $queryBuilder;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'employee_shift_sub.employee_number', 'direction' => 'ASC'],
            ['field' => 'employee_shift_sub.shift_start_date', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, ShiftAssignment::class);
    }

    public function selection($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'shifts.code', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->model::query()->getQuery()
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

        return $this->hydratePaginationItems($paginator, ShiftAssignment::class);
    }

    public function shiftsByEmployees($filters): LengthAwarePaginator
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
            ->leftJoin('employee_shift', 'employee_shift.employee_id', '=', 'employee_sub.id')
            ->leftJoin('shifts', 'shifts.id', '=', 'employee_shift.shift_id')
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                'employee_sub.id AS employee_id',
                'employee_sub.number AS employee_number',
                'employee_sub.full_name AS employee_full_name',
                DB::raw("MAX(employee_sub.employment_status_active) AS employee_employment_status_active"),
                DB::raw("MAX(employee_sub.current_employment_status) AS employee_current_employment_status"),
                DB::raw("MAX(employee_sub.current_employment_type) AS employee_current_employment_type"),
                DB::raw("GROUP_CONCAT(shifts.code ORDER BY employee_shift.start_date ASC) AS assigned_shift_codes"),
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);
        $this->setGroupsOnBuilder($queryBuilder, $groups);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, ShiftsByEmployees::class);
    }

    public function sync($employeeIds, $shiftIds, $pivotData): void
    {
        foreach ($employeeIds as $employeeId) {

            $employee = Employee::query()->find($employeeId);

            $sync = collect($shiftIds)->mapWithKeys(fn ($id) => [$id => $pivotData])->toArray();

            $employee->shifts()->sync($sync);
        }
    }

    public function update($identifier, $attributes)
    {
        $updateErrors = [];

        $employeeShift = $this->model::query()->findOrfail($identifier);

        $employee = $employeeShift->employee;

        $firstEmployeeShift = $this->model()::query()->where('employee_id', $employee->id)->orderBy('start_date')->first();
        $latestEmployeeShift = $this->model()::query()->where('employee_id', $employee->id)->whereNot('shift_id', $employeeShift->shift_id)->orderByDesc('start_date')->get()->first();

        $syncItemStartDate = Carbon::parse($attributes['start_date']);
        $syncItemStatedShiftEndDate = $attributes['stated_shift_end_date'];
        $syncItemEndDate = Carbon::parse($attributes['end_date']);

        if(!empty($latestEmployeeShift)){

            $updateErrors = array_merge($updateErrors, $this->validateShiftAssignment($employee, $firstEmployeeShift, $latestEmployeeShift, $syncItemStatedShiftEndDate, $syncItemStartDate, $syncItemEndDate));

            if(empty($updateErrors)){
                $employeeShift = $employeeShift->update($attributes);
            }

        } else {
            $employeeShift = $employeeShift->update($attributes);
        }

        return [
            $employeeShift,
            $updateErrors
        ];
    }

    public function syncWithoutDetaching($employeeIds, $shiftIds, $pivotData): array
    {
        $syncErrors = [];

        foreach ($employeeIds as $employeeId) {

            $employee = Employee::query()->find($employeeId);
            $existingEmployeeShiftIds = $this->model()::query()->where('employee_id', $employee->id)->pluck('shift_id')->toArray();
            $firstEmployeeShift = $this->model()::query()->where('employee_id', $employee->id)->orderBy('start_date')->first();
            $latestEmployeeShift = $this->model()::query()->where('employee_id', $employee->id)->orderByDesc('start_date')->get()->first();

            $sync = collect($shiftIds)->mapWithKeys(fn ($id) => [$id => $pivotData])->toArray();
            $syncItem = null;

            foreach ($shiftIds as $shiftId){
                $syncItem = $sync[$shiftId];
                break;
            }

            if (count(array_intersect($shiftIds, $existingEmployeeShiftIds)) > 0) {
                $syncErrors[] = [
                    'employee_number' => $employee->number, 'employee_full_name' => $employee->full_name,
                    'error' => 'Shift already assigned',
                ];
                continue;
            }

            if(empty($syncItem)){
                $syncErrors[] = [
                    'employee_number' => $employee->number, 'employee_full_name' => $employee->full_name,
                    'error' => 'Sync error',
                ];
                continue;
            }

            $syncItemStartDate = Carbon::parse($syncItem['start_date']);
            $syncItemStatedShiftEndDate = $syncItem['stated_shift_end_date'];
            $syncItemEndDate = Carbon::parse($syncItem['end_date']);


            if(!empty($latestEmployeeShift)){

                $syncErrors = array_merge($syncErrors, $this->validateShiftAssignment($employee, $firstEmployeeShift, $latestEmployeeShift, $syncItemStatedShiftEndDate, $syncItemStartDate, $syncItemEndDate));

            } else {

                $employee->shifts()->syncWithoutDetaching($sync);
            }

            if(empty($syncErrors)){
               $employee->shifts()->syncWithoutDetaching($sync);
            }
        }

        return $syncErrors;
    }

    public function validateShiftAssignment($employee, $firstEmployeeShift, $latestEmployeeShift, $syncItemStatedShiftEndDate, $syncItemStartDate, $syncItemEndDate): array
    {
        $syncErrors = [];

        if(!$latestEmployeeShift->stated_shift_end_date){

            if($syncItemStatedShiftEndDate){

                if($syncItemEndDate->gte($firstEmployeeShift->start_date)){

                    $syncErrors[] = [
                        'employee_number' => $employee->number, 'employee_full_name' => $employee->full_name,
                        'error' => 'Shift overlaps with existing shift(s)',
                    ];

                    return $syncErrors;
                }
            }

            if(!$syncItemStatedShiftEndDate){
                $syncErrors[] = [
                    'employee_number' => $employee->number, 'employee_full_name' => $employee->full_name,
                    'error' => 'Shift overlaps with existing shift(s)',
                ];

                return $syncErrors;
            }
        }

        if($latestEmployeeShift->stated_shift_end_date){

            if(!$syncItemStatedShiftEndDate && $syncItemStartDate->lte($latestEmployeeShift->end_date)){
                $syncErrors[] = [
                    'employee_number' => $employee->number, 'employee_full_name' => $employee->full_name,
                    'error' => 'Shift overlaps with existing shift(s)',
                ];

                return $syncErrors;
            }

            if($syncItemStatedShiftEndDate){

                if(empty($syncItemEndDate)){
                    $syncErrors[] = [
                        'employee_number' => $employee->number, 'employee_full_name' => $employee->full_name,
                        'error' => 'Sync error',
                    ];

                    return $syncErrors;
                }

                if($syncItemStartDate->lte($latestEmployeeShift->end_date)){

                    if($syncItemStartDate->gte($firstEmployeeShift->start_date) || $syncItemEndDate->gte($firstEmployeeShift->start_date)){
                        $syncErrors[] = [
                            'employee_number' => $employee->number, 'employee_full_name' => $employee->full_name,
                            'error' => 'Shift overlaps with existing shift(s)',
                        ];

                        return $syncErrors;
                    }
                }
            }
        }

        return $syncErrors;
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
