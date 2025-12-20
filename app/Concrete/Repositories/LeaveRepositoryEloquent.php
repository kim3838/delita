<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\LeaveRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class LeaveRepositoryEloquent extends BaseRepositoryEloquent implements LeaveRepository
{
    public function model(): string
    {
        return Leave::class;
    }

    public function baseQueryBuilder($filters, $orders = []): QueryBuilder
    {
        $employeeRepositoryFilter = clone $filters;

        $employeeQueryBuilder = App::make(EmployeeRepository::class)->baseQueryBuilder($employeeRepositoryFilter, []);

        $queryBuilder = $this->model::query()->getQuery()
            ->joinSub($employeeQueryBuilder, 'employee_sub', function ($join) {
                $join->on('employee_sub.id', '=', 'leaves.employee_id');
            })
            ->when(!empty($filters->leave_type_ids) && is_array($filters->leave_type_ids), function ($builder) use ($filters) {
                $builder->whereIn('leaves.leave_type_id', $filters->leave_type_ids);
            })
            ->when((
                (isset($filters->date_from) && Carbon::createFromFormat('Y-m-d', $filters->date_from)) &&
                (isset($filters->date_to) && Carbon::createFromFormat('Y-m-d', $filters->date_to))
            ),function($builder) use ($filters){
                $builder->whereBetween('leaves.date', [$filters->date_from, $filters->date_to]);
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                "employee_sub.number AS employee_number",

                "leaves.id AS id",
                "leaves.ulid AS ulid",
                "leaves.employee_id AS employee_id",
                "leaves.leave_type_id AS leave_type_id",
                "leaves.date AS date",
            ]);

        return $queryBuilder;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'employee_sub.number', 'direction' => 'ASC'],
            ['field' => 'leaves.date', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }
}
