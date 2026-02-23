<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\SalaryStatementAttendanceRepository;
use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\SalaryStatementAttendance;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class SalaryStatementAttendanceRepositoryEloquent extends BaseRepositoryEloquent implements SalaryStatementAttendanceRepository
{
    public function model(): string
    {
        return SalaryStatementAttendance::class;
    }

    public function baseQueryBuilder($filters, $orders = [], $relations = []): QueryBuilder
    {
        $queryBuilder = $this->model::query()->getQuery()
            ->when(in_array('salary_statement', $relations), function ($builder) use($filters) {

                $salaryStatementRepositoryFilter = clone $filters;
                unset($salaryStatementRepositoryFilter->date);

                $salaryStatementQueryBuilder = App::make(SalaryStatementRepository::class)->baseQueryBuilder($salaryStatementRepositoryFilter, [], ['payroll']);

                $builder->joinSub($salaryStatementQueryBuilder, 'salary_statement_sub', function ($join) {
                    $join->on('salary_statement_sub.id', '=', 'salary_statement_attendances.salary_statement_id');
                });
            })
            ->when(!empty($filters->salary_statement_ids) && is_array($filters->salary_statement_ids), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("salary_statement_attendances.salary_statement_id"), $filters->salary_statement_ids);
            })
            ->when(isset($filters->date), function ($builder, $value) {
                $builder->whereDate(DB::raw("salary_statement_attendances.date"), $value);
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

    public function list($filters, $relations = []): Collection
    {
        $orders = [
            ['field' => 'salary_statement_attendances.date', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders, $relations);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }
}
