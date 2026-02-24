<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\PayrollRepository;
use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\SalaryStatement;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class SalaryStatementRepositoryEloquent extends BaseRepositoryEloquent implements SalaryStatementRepository
{
    public function model(): string
    {
        return SalaryStatement::class;
    }

    public function baseQueryBuilder($filters, $orders = [], $relations = []): QueryBuilder
    {
        $employeeRepositoryFilter = clone $filters;
        if(isset($employeeRepositoryFilter->employee_search)){
            $employeeRepositoryFilter->search = $employeeRepositoryFilter->employee_search;
        }
        unset($employeeRepositoryFilter->payroll_search);
        unset($employeeRepositoryFilter->employee_search);

        $employeeQueryBuilder = App::make(EmployeeRepository::class)->baseQueryBuilder($employeeRepositoryFilter, [], $relations);

        $queryBuilder = $this->model::query()->getQuery()
            ->when(in_array('payroll', $relations), function ($builder) use($filters) {

                $payrollRepositoryFilter = clone $filters;
                if(isset($payrollRepositoryFilter->payroll_search)){$payrollRepositoryFilter->search = $payrollRepositoryFilter->payroll_search;}
                if(isset($payrollRepositoryFilter->payroll_year)){$payrollRepositoryFilter->year = $payrollRepositoryFilter->payroll_year;}
                if(isset($payrollRepositoryFilter->payroll_month)){$payrollRepositoryFilter->month = $payrollRepositoryFilter->payroll_month;}
                if(isset($payrollRepositoryFilter->payroll_pay_frequency)){$payrollRepositoryFilter->pay_frequency = $payrollRepositoryFilter->payroll_pay_frequency;}
                if(isset($payrollRepositoryFilter->payroll_frequency_sequence)){$payrollRepositoryFilter->frequency_sequence = $payrollRepositoryFilter->payroll_frequency_sequence;}

                unset($payrollRepositoryFilter->payroll_search);
                unset($payrollRepositoryFilter->employee_search);
                unset($payrollRepositoryFilter->payroll_year);
                unset($payrollRepositoryFilter->payroll_month);
                unset($payrollRepositoryFilter->payroll_pay_frequency);
                unset($payrollRepositoryFilter->payroll_frequency_sequence);

                $payrollQueryBuilder = App::make(PayrollRepository::class)->baseQueryBuilder($payrollRepositoryFilter);

                $builder->joinSub($payrollQueryBuilder, 'payroll_sub', function ($join) {
                    $join->on('payroll_sub.id', '=', 'salary_statements.payroll_id');
                });
            })
            ->joinSub($employeeQueryBuilder, 'employee_sub', function ($join) {
                $join->on('employee_sub.id', '=', 'salary_statements.employee_id');
            })
            ->when(!empty($filters->salary_statement_ids) && is_array($filters->salary_statement_ids), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("salary_statements.id"), $filters->salary_statement_ids);
            })
            ->when(!empty($filters->not_salary_statement_ids) && is_array($filters->not_salary_statement_ids), function ($builder) use ($filters) {
                $builder->whereNotIn(DB::raw("salary_statements.id"), $filters->not_salary_statement_ids);
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),

                ...(in_array('payroll', $relations) ? [
                    "payroll_sub.ulid AS payroll_ulid",
                    "payroll_sub.company_id AS payroll_company_id",
                    "payroll_sub.number AS payroll_number",
                    "payroll_sub.year AS payroll_year",
                    "payroll_sub.month AS payroll_month",
                    "payroll_sub.pay_frequency AS payroll_pay_frequency",
                    "payroll_sub.frequency_sequence AS payroll_frequency_sequence",
                    "payroll_sub.start_date AS payroll_start_date",
                    "payroll_sub.end_date AS payroll_end_date",
                    "payroll_sub.remarks AS payroll_remarks",
                    "payroll_sub.status AS payroll_status",
                ] : []),

                "employee_sub.number AS employee_number",

                ...(in_array('current_employment_profile', $relations) ? [
                    'employee_sub.employment_status_active AS employee_employment_status_active',
                    'employee_sub.current_employment_status AS employee_current_employment_status',
                    'employee_sub.current_employment_type AS employee_current_employment_type',
                ] : []),

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
            ...(in_array('payroll', $relations) ? [
                ['field' => 'payroll_sub.id', 'direction' => 'ASC']
            ] : []),

            ['field' => 'employee_sub.number', 'direction' => 'ASC'],
        ]: $orders;

        $queryBuilder = $this->baseQueryBuilder($filters, $orders, $relations);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function list($filters, $relations = []): Collection
    {
        $orders = empty($orders) ? [
            ...(in_array('payroll', $relations) ? [
                ['field' => 'payroll_sub.id', 'direction' => 'ASC']
            ] : []),

            ['field' => 'employee_sub.number', 'direction' => 'ASC'],
        ]: $orders;

        $queryBuilder = $this->baseQueryBuilder($filters, $orders, $relations);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }

    public function show($identifier)
    {
        $queryBuilder = $this->model::query()->where('ulid', $identifier);

        return $queryBuilder->firstOrFail();
    }
}
