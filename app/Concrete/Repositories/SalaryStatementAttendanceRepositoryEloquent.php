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
            ->when(isset($filters->date), function ($builder, $value) use($filters) {
                $builder->whereDate(DB::raw("salary_statement_attendances.date"), $filters->date);
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

                ...(in_array('salary_statement', $relations) ? [
                    DB::raw("salary_statement_sub.payroll_id as payroll_id"),
                    DB::raw("salary_statement_sub.payroll_ulid as payroll_ulid"),
                    DB::raw("salary_statement_sub.payroll_company_id as payroll_company_id"),
                    DB::raw("salary_statement_sub.payroll_number as payroll_number"),
                    DB::raw("salary_statement_sub.payroll_year as payroll_year"),
                    DB::raw("salary_statement_sub.payroll_month as payroll_month"),
                    DB::raw("salary_statement_sub.payroll_pay_frequency as payroll_pay_frequency"),
                    DB::raw("salary_statement_sub.payroll_frequency_sequence as payroll_frequency_sequence"),
                    DB::raw("salary_statement_sub.payroll_start_date as payroll_start_date"),
                    DB::raw("salary_statement_sub.payroll_end_date as payroll_end_date"),
                    DB::raw("salary_statement_sub.payroll_remarks as payroll_remarks"),
                    DB::raw("salary_statement_sub.payroll_status as payroll_status"),
                ] : []),
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
