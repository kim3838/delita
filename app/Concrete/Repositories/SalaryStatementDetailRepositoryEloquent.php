<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\SalaryStatementDetailRepository;
use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\SalaryStatementDetail;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),

                "salary_statement_details.id AS id",
                "salary_statement_details.salary_statement_id AS salary_statement_id",

                "salary_statement_details.formulable_type",
                "salary_statement_details.component_type",
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

    public function list($filters, $relations = []): Collection
    {
        $orders = [
            ['field' => 'salary_statement_details.formulable_type', 'direction' => 'ASC'],
            ['field' => 'salary_statement_details.component_type', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders, $relations);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }
}
