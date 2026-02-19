<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\PayrollRepository;
use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\SalaryStatement;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class SalaryStatementRepositoryEloquent extends BaseRepositoryEloquent implements SalaryStatementRepository
{
    public function model(): string
    {
        return SalaryStatement::class;
    }

    public function baseQueryBuilder($filters, $orders = []): QueryBuilder
    {
        $employeeRepositoryFilter = clone $filters;

        $employeeQueryBuilder = App::make(EmployeeRepository::class)->baseQueryBuilder($employeeRepositoryFilter, [], ['current_employment_profile']);

        $payrollRepositoryFilter = clone $filters;

        $payrollQueryBuilder = App::make(PayrollRepository::class)->baseQueryBuilder($payrollRepositoryFilter, []);

        $queryBuilder = $this->model::query()->getQuery()
            ->joinSub($payrollQueryBuilder, 'payroll_sub', function ($join) {
                $join->on('payroll_sub.id', '=', 'salary_statements.payroll_id');
            })
            ->joinSub($employeeQueryBuilder, 'employee_sub', function ($join) {
                $join->on('employee_sub.id', '=', 'salary_statements.employee_id');
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),

                "payroll_sub.id AS payroll_id",
                "payroll_sub.company_id AS company_id",
                "payroll_sub.number AS payroll_number",
                "payroll_sub.year AS payroll_year",
                "payroll_sub.month AS payroll_month",
                "payroll_sub.pay_frequency AS payroll_pay_frequency",
                "payroll_sub.frequency_sequence AS payroll_frequency_sequence",
                "payroll_sub.start_date AS payroll_start_date",
                "payroll_sub.end_date AS payroll_end_date",
                "payroll_sub.remarks AS payroll_remarks",
                "payroll_sub.status AS payroll_status",

                "employee_sub.number AS employee_number",
                'employee_sub.employment_status_active AS employee_employment_status_active',
                'employee_sub.current_employment_status AS employee_current_employment_status',
                'employee_sub.current_employment_type AS employee_current_employment_type',

                "salary_statements.id AS id",
                "salary_statements.ulid AS ulid",
                "salary_statements.payroll_id AS payroll_id",
                "salary_statements.employee_id AS employee_id",

                "salary_statements.total_days",
                "salary_statements.total_day_offs",
                "salary_statements.total_working_days",
                "salary_statements.total_regular_work_days",
                "salary_statements.total_working_rest_days",
                "salary_statements.total_special_holidays",
                "salary_statements.total_legal_holidays",
                "salary_statements.total_full_present",
                "salary_statements.total_present_with_irregularity",
                "salary_statements.total_leave_without_pay",
                "salary_statements.total_leave_with_pay",
                "salary_statements.total_absent",

                "salary_statements.taxable",
                "salary_statements.nontaxable",
                "salary_statements.contribution",
                "salary_statements.withholding_tax",
                "salary_statements.deduction",
                "salary_statements.net",
            ]);

        return $queryBuilder;
    }

    public function paginate($filters, $relations = [], $orders = []): LengthAwarePaginator
    {
        $orders = empty($orders) ? [
            ['field' => 'payroll_sub.id', 'direction' => 'ASC'],
            ['field' => 'employee_sub.number', 'direction' => 'ASC'],
        ]: $orders;

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }
}
