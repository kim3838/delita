<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\SalaryStatementAttendanceRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\SalaryStatementAttendance;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalaryStatementAttendanceRepositoryEloquent extends BaseRepositoryEloquent implements SalaryStatementAttendanceRepository
{
    public function model(): string
    {
        return SalaryStatementAttendance::class;
    }

    public function baseQueryBuilder($filters, $orders = []): QueryBuilder
    {
        $queryBuilder = $this->model::query()->getQuery()
            ->when(!empty($filters->salary_statement_ids) && is_array($filters->salary_statement_ids), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("salary_statement_attendances.salary_statement_id"), $filters->salary_statement_ids);
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),

                "salary_statement_attendances.id AS id",
                "salary_statement_attendances.ulid AS ulid",
                "salary_statement_attendances.salary_statement_id AS salary_statement_id",

                "salary_statement_attendances.attendance_id",
                "salary_statement_attendances.date",
                "salary_statement_attendances.status",
                "salary_statement_attendances.day_type",
            ]);

        return $queryBuilder;
    }

    public function list($filters): Collection
    {
        $orders = [
            ['field' => 'salary_statement_attendances.date', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }
}
