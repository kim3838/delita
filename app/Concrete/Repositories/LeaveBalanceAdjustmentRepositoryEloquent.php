<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\LeaveBalanceAdjustmentRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\LeaveBalanceAdjustment;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class LeaveBalanceAdjustmentRepositoryEloquent extends BaseRepositoryEloquent implements LeaveBalanceAdjustmentRepository
{
    public function model(): string
    {
        return LeaveBalanceAdjustment::class;
    }

    public function baseQueryBuilder($filters, $orders = []): QueryBuilder
    {
        $employeeRepositoryFilter = clone $filters;

        $employeeQueryBuilder = App::make(EmployeeRepository::class)->baseQueryBuilder($employeeRepositoryFilter, []);

        $queryBuilder = $this->model::query()->getQuery()
            ->joinSub($employeeQueryBuilder, 'employee_sub', function ($join) {
                $join->on('employee_sub.id', '=', 'leave_balance_adjustments.employee_id');
            })
            ->when(!empty($filters->leave_type_ids) && is_array($filters->leave_type_ids), function ($builder) use ($filters) {
                $builder->whereIn('leave_balance_adjustments.leave_type_id', $filters->leave_type_ids);
            })
            ->when((
                (isset($filters->date_from) && Carbon::createFromFormat('Y-m-d', $filters->date_from)) &&
                (isset($filters->date_to) && Carbon::createFromFormat('Y-m-d', $filters->date_to))
            ),function($builder) use ($filters){
                $builder->whereBetween('leave_balance_adjustments.effective_date', [$filters->date_from, $filters->date_to]);
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                "employee_sub.number AS employee_number",

                "leave_balance_adjustments.id AS id",
                "leave_balance_adjustments.ulid AS ulid",
                "leave_balance_adjustments.employee_id AS employee_id",
                "leave_balance_adjustments.leave_type_id AS leave_type_id",
                "leave_balance_adjustments.type AS type",
                "leave_balance_adjustments.balance AS balance",
                "leave_balance_adjustments.remarks AS remarks",
                "leave_balance_adjustments.effective_date AS effective_date",
            ]);

        return $queryBuilder;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'employee_sub.number', 'direction' => 'ASC'],
            ['field' => 'leave_balance_adjustments.effective_date', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function show($identifier)
    {
        $queryBuilder = $this->model::query()->where('ulid', $identifier);

        return $queryBuilder->firstOrFail();
    }

    public function update($identifier, $attributes)
    {
        $model = $this->show($identifier);

        $model->update($attributes);

        return $model;
    }
}
