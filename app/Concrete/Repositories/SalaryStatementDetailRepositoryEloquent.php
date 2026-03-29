<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\SalaryStatementDetailRepository;
use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\SalaryStatementDetail;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class SalaryStatementDetailRepositoryEloquent extends BaseRepositoryEloquent implements SalaryStatementDetailRepository
{
    public function model(): string
    {
        return SalaryStatementDetail::class;
    }

    public function baseQueryBuilder($filters, $orders = [], $relations = []): QueryBuilder
    {
        $queryBuilder = $this->model::query()->getQuery()
            ->when(in_array('salary_statement', $relations), function ($builder) use($filters) {

                $salaryStatementRepositoryFilter = clone $filters;

                $salaryStatementQueryBuilder = App::make(SalaryStatementRepository::class)->baseQueryBuilder($salaryStatementRepositoryFilter, [], ['payroll']);

                $builder->joinSub($salaryStatementQueryBuilder, 'salary_statement_sub', function ($join) {
                    $join->on('salary_statement_sub.id', '=', 'salary_statement_details.salary_statement_id');
                });
            })
            ->when(!empty($filters->formulable_types) && is_array($filters->formulable_types), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("salary_statement_details.formulable_type"), $filters->formulable_types);
            })
            ->when(!empty($filters->component_types) && is_array($filters->component_types), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("salary_statement_details.component_type"), $filters->component_types);
            })
            ->when(!empty($filters->component_sub_types) && is_array($filters->component_sub_types), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("salary_statement_details.component_sub_type"), $filters->component_sub_types);
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),

                "salary_statement_details.id AS id",
                "salary_statement_details.salary_statement_id AS salary_statement_id",

                ...(in_array('salary_statement', $relations) ? [
                    "salary_statement_sub.payroll_ulid",
                    "salary_statement_sub.payroll_company_id",
                    "salary_statement_sub.payroll_number",
                    "salary_statement_sub.payroll_year",
                    "salary_statement_sub.payroll_month",
                    "salary_statement_sub.payroll_pay_frequency",
                    "salary_statement_sub.payroll_frequency_sequence",
                    "salary_statement_sub.payroll_start_date",
                    "salary_statement_sub.payroll_end_date",
                    "salary_statement_sub.payroll_remarks",
                    "salary_statement_sub.payroll_status",
                ] : []),

                "salary_statement_sub.employee_ulid AS employee_ulid",
                "salary_statement_sub.employee_number AS employee_number",
                "salary_statement_sub.employee_full_name AS employee_full_name",

                ...(in_array('salary_statement', $relations) ? [
                    "salary_statement_sub.company_currency_code AS company_currency_code",
                ]: []),

                "salary_statement_details.formulable_type",
                "salary_statement_details.component_type",
                "salary_statement_details.component_sub_type",
                "salary_statement_details.component_name",
                "salary_statement_details.component_values",
                "salary_statement_details.taxable",
                "salary_statement_details.nontaxable",
                "salary_statement_details.contribution",
                "salary_statement_details.withholding_tax",
                "salary_statement_details.deduction",
                "salary_statement_details.net",
            ]);

        return $queryBuilder;
    }

    public function paginate($filters, $relations = [], $orders = []): LengthAwarePaginator
    {
        $orders = empty($orders) ? [
            ['field' => 'salary_statement_details.id', 'direction' => 'ASC'],
        ]: $orders;

        $queryBuilder = $this->baseQueryBuilder($filters, $orders, $relations);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function list($filters, $relations = []): Collection
    {
        $orders = [
            ['field' => 'salary_statement_details.id', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders, $relations);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }
}
