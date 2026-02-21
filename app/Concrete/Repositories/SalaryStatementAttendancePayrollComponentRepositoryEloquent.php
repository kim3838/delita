<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\SalaryStatementAttendancePayrollComponentRepository;
use App\Blueprint\Repositories\SalaryStatementAttendanceRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\SalaryStatementAttendancePayrollComponent;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class SalaryStatementAttendancePayrollComponentRepositoryEloquent extends BaseRepositoryEloquent implements SalaryStatementAttendancePayrollComponentRepository
{
    public function model(): string
    {
        return SalaryStatementAttendancePayrollComponent::class;
    }

    public function baseQueryBuilder($filters, $orders = []): QueryBuilder
    {
        $salaryStatementAttendanceRepositoryFilter = clone $filters;

        $salaryStatementAttendanceQueryBuilder = App::make(SalaryStatementAttendanceRepository::class)->baseQueryBuilder($salaryStatementAttendanceRepositoryFilter);

        $queryBuilder = $this->model::query()->getQuery()
            ->joinSub($salaryStatementAttendanceQueryBuilder, 'salary_statement_attendance_sub', function ($join) use ($filters) {
                $join->on('salary_statement_attendance_sub.id', '=', 'salary_statement_attendance_payroll_components.salary_statement_attendance_id')
                    ->when(!empty($filters->salary_statement_attendance_ids) && is_array($filters->salary_statement_attendance_ids), function ($builder) use ($filters) {
                        $builder->whereIn(DB::raw("salary_statement_attendance_payroll_components.salary_statement_attendance_id"), $filters->salary_statement_attendance_ids);
                    });
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                "salary_statement_attendance_payroll_components.id",
                "salary_statement_attendance_payroll_components.salary_statement_attendance_id",

                "salary_statement_attendance_payroll_components.formulable_type",
                "salary_statement_attendance_payroll_components.component_type",
                "salary_statement_attendance_payroll_components.component_key",
                "salary_statement_attendance_payroll_components.component_name",
                "salary_statement_attendance_payroll_components.regular_pay",
                "salary_statement_attendance_payroll_components.night_differential_pay",
                "salary_statement_attendance_payroll_components.rest_day_pay",
                "salary_statement_attendance_payroll_components.total",
            ]);

        return $queryBuilder;
    }

    public function list($filters): Collection
    {
        $orders = [
            ['field' => 'salary_statement_attendance_payroll_components.formulable_type', 'direction' => 'ASC'],
            ['field' => 'salary_statement_attendance_payroll_components.component_type', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }
}
