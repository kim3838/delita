<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\SalaryStatementAttendancePayrollComponentRepository;
use App\Blueprint\Repositories\SalaryStatementAttendanceRepository;
use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\SalaryStatementAttendance;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
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
            ->when(in_array('payroll_components', $relations), function ($builder) use($filters) {

                $salaryStatementAttendancePayrollComponentRepositoryFilter = clone $filters;
                $this->removeFieldsFromFilter($salaryStatementAttendancePayrollComponentRepositoryFilter, ['employee_ids', 'date_from', 'date_to']);

                $salaryStatementAttendancePayrollComponentQueryBuilder = App::make(SalaryStatementAttendancePayrollComponentRepository::class)->baseQueryBuilder($salaryStatementAttendancePayrollComponentRepositoryFilter, [], []);

                $salaryStatementAttendancePayrollComponentQueryBuilder = $this->queryAsSub($salaryStatementAttendancePayrollComponentQueryBuilder, 'payroll_components_sub')
                    ->select([
                        'payroll_components_sub.salary_statement_attendance_id',
                        'payroll_components_sub.formulable_type',
                        'payroll_components_sub.component_type',
                        'payroll_components_sub.component_name',
                        'payroll_components_sub.regular_pay',
                        'payroll_components_sub.night_differential_pay',
                        'payroll_components_sub.rest_day_pay',
                        'payroll_components_sub.total',
                    ]);

                $builder->leftJoinSub($salaryStatementAttendancePayrollComponentQueryBuilder, 'payroll_components_sub', function ($join) {
                    $join->on('payroll_components_sub.salary_statement_attendance_id', '=', 'salary_statement_attendances.id');
                });
            })
            ->when(!empty($filters->salary_statement_ids) && is_array($filters->salary_statement_ids), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("salary_statement_attendances.salary_statement_id"), $filters->salary_statement_ids);
            })
            ->when(!empty($filters->statement_date_statuses) && is_array($filters->statement_date_statuses), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("salary_statement_attendances.status"), $filters->statement_date_statuses);
            })
            ->when(!empty($filters->statement_date_day_types) && is_array($filters->statement_date_day_types), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("salary_statement_attendances.day_type"), $filters->statement_date_day_types);
            })
            ->when(isset($filters->date), function ($builder, $value) use($filters) {
                $builder->whereDate(DB::raw("salary_statement_attendances.date"), $filters->date);
            })
            ->when((
                (isset($filters->date_from) && Carbon::createFromFormat('Y-m-d', $filters->date_from)) &&
                (isset($filters->date_to) && Carbon::createFromFormat('Y-m-d', $filters->date_to))
            ),function($builder) use ($filters){
                $builder->whereBetween('salary_statement_attendances.date', [$filters->date_from, $filters->date_to]);
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

                ...(in_array('salary_statement', $relations) && !in_array('payroll_components', $relations) ? [
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

                ...(in_array('salary_statement', $relations) && in_array('payroll_components', $relations) ? [
                    DB::raw("salary_statement_sub.employee_id as employee_id"),
                ] : []),

                ...(in_array('payroll_components', $relations) ? [
                    DB::raw("payroll_components_sub.formulable_type"),
                    DB::raw("payroll_components_sub.component_type"),
                    DB::raw("payroll_components_sub.component_name"),
                    DB::raw("payroll_components_sub.regular_pay"),
                    DB::raw("payroll_components_sub.night_differential_pay"),
                    DB::raw("payroll_components_sub.rest_day_pay"),
                    DB::raw("payroll_components_sub.total"),
                ] : []),
            ]);

        return $queryBuilder;
    }

    public function paginate($filters, $relations = [], $orders = []): LengthAwarePaginator
    {
        $orders = empty($orders) ? [
            ...(in_array('payroll_components', $relations) ? [
                ['field' => 'salary_statement_attendances.date', 'direction' => 'ASC'],
                ['field' => 'payroll_components_sub.formulable_type', 'direction' => 'ASC'],
                ['field' => 'payroll_components_sub.component_type', 'direction' => 'ASC'],
            ] : []),

            ['field' => 'salary_statement_attendances.date', 'direction' => 'ASC'],
        ]: $orders;

        $queryBuilder = $this->baseQueryBuilder($filters, $orders, $relations);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
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
