<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\PayrollRepository;
use App\Blueprint\Repositories\SalaryStatementDetailRepository;
use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\Compensation;
use App\Enums\Deduction;
use App\Enums\Formulable;
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
                if(isset($payrollRepositoryFilter->payroll_from_month)){$payrollRepositoryFilter->from_month = $payrollRepositoryFilter->payroll_from_month;}
                if(isset($payrollRepositoryFilter->payroll_to_month)){$payrollRepositoryFilter->to_month = $payrollRepositoryFilter->payroll_to_month;}

                unset($payrollRepositoryFilter->employee_search);

                $payrollQueryBuilder = App::make(PayrollRepository::class)->baseQueryBuilder($payrollRepositoryFilter);

                $builder->joinSub($payrollQueryBuilder, 'payroll_sub', function ($join) {
                    $join->on('payroll_sub.id', '=', 'salary_statements.payroll_id');
                });
            })
            ->when(in_array('detail_totals', $relations), function ($builder) use($filters) {

                $salaryStatementDetailRepositoryFilter = (object)[];

                $salaryStatementDetailQueryBuilder = App::make(SalaryStatementDetailRepository::class)->baseQueryBuilder($salaryStatementDetailRepositoryFilter, [], [])
                    ->select([
                        'salary_statement_details.salary_statement_id',
                        DB::raw("component_values->>'$.employer_share.total' AS total_employer_share"),
                        DB::raw("
                            CASE WHEN salary_statement_details.formulable_type = ". Formulable::EARNINGS->value ." AND salary_statement_details.component_type IN (". implode(",", [Compensation::BASIC_PAY->value, Compensation::LEAVE_PAY->value])  .")
                            THEN component_values->>'$.regular_pay'
                            ELSE '0.000000'
                            END AS total_basic_pay
                        "),
                        DB::raw("
                            CASE WHEN salary_statement_details.formulable_type = ". Formulable::DEDUCTIONS->value ." AND salary_statement_details.component_type IN (". implode(",", [Deduction::DEDUCTION->value])  .")
                            THEN component_values->>'$.regular_pay'
                            ELSE '0.000000'
                            END AS total_nonstatutory_deduction
                        "),
                    ]);

                //Aggregate detail totals by salary statement id
                $salaryStatementDetailTotalBuilder = $this->queryAsSub($salaryStatementDetailQueryBuilder, 'details_total_sub')
                    ->select([
                        'details_total_sub.salary_statement_id',
                        DB::raw("SUM(details_total_sub.total_employer_share) AS total_employer_contribution_share"),
                        DB::raw("SUM(details_total_sub.total_basic_pay) AS total_basic_pay"),
                        DB::raw("SUM(details_total_sub.total_nonstatutory_deduction) AS total_nonstatutory_deduction"),
                    ])->groupBy('salary_statement_id');

                //Get actual basic pay
                $salaryStatementDetailTotalBuilder = $this->queryAsSub($salaryStatementDetailTotalBuilder, 'details_total_sub')
                    ->select([
                        'details_total_sub.salary_statement_id',
                        DB::raw("details_total_sub.total_employer_contribution_share"),
                        DB::raw("details_total_sub.total_basic_pay"),
                        DB::raw("details_total_sub.total_nonstatutory_deduction"),
                        DB::raw("details_total_sub.total_basic_pay - details_total_sub.total_nonstatutory_deduction AS total_basic_gross"),
                    ]);

                $builder->joinSub($salaryStatementDetailTotalBuilder, 'details_total_sub', function ($join) {
                    $join->on('details_total_sub.salary_statement_id', '=', 'salary_statements.id');
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

                ...(in_array('detail_totals', $relations) ? [
                    "details_total_sub.total_employer_contribution_share AS total_employer_contribution_share",
                    "details_total_sub.total_basic_pay AS total_basic_pay",
                    "details_total_sub.total_nonstatutory_deduction AS total_nonstatutory_deduction",
                    "details_total_sub.total_basic_gross AS total_basic_gross",
                ] : []),

                "employee_sub.number AS employee_number",
                "employee_sub.full_name AS employee_full_name",

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
                ['field' => 'payroll_sub.year', 'direction' => 'ASC'],
                ['field' => 'payroll_sub.month', 'direction' => 'ASC'],
                ['field' => 'payroll_sub.pay_frequency', 'direction' => 'ASC'],
                ['field' => 'payroll_sub.frequency_sequence', 'direction' => 'ASC'],
                ['field' => 'payroll_sub.start_date', 'direction' => 'ASC'],

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
