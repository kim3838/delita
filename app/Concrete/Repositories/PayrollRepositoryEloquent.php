<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\PayrollRepository;
use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Payroll;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class PayrollRepositoryEloquent extends BaseRepositoryEloquent implements PayrollRepository
{
    public function model(): string
    {
        return Payroll::class;
    }

    public function baseQueryBuilder($filters, $orders = [], $relations = []): QueryBuilder
    {
        $groups = [
            'payrolls.id'
        ];

        $queryBuilder = $this->model::query()->getQuery()
            ->when(in_array('salary_statement', $relations), function ($builder) use($filters) {

                $salaryStatementRepositoryFilter = clone $filters;

                $salaryStatementQueryBuilder = App::make(SalaryStatementRepository::class)->baseQueryBuilder($salaryStatementRepositoryFilter, [], []);

                $builder->joinSub($salaryStatementQueryBuilder, 'salary_statement_sub', function ($join) {
                    $join->on('salary_statement_sub.payroll_id', '=', 'payrolls.id');
                });
            })
            ->when(!empty($filters->company_ids) && is_array($filters->company_ids), function ($builder) use ($filters) {
                $builder->whereIn('payrolls.company_id', $filters->company_ids);
            })
            ->when(!empty($filters->payroll_ids) && is_array($filters->payroll_ids), function ($builder) use ($filters) {
                $builder->whereIn('payrolls.id', $filters->payroll_ids);
            })
            ->when($filters->search ?? false, function ($builder, $value) {
                $builder->where(function ($clause) use ($value) {
                    $clause->where('payrolls.number', 'LIKE', "%$value%");
                });
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                "payrolls.id",
                "payrolls.ulid",
                "payrolls.company_id",
                "payrolls.number",
                "payrolls.year",
                "payrolls.month",
                "payrolls.pay_frequency",
                "payrolls.frequency_sequence",
                "payrolls.start_date",
                "payrolls.end_date",
                "payrolls.remarks",
                "payrolls.status",

                ...(in_array('salary_statement', $relations) ? [
                    DB::raw("SUM(salary_statement_sub.net) AS total_salary_statement_net"),
                ] : []),
            ]);

        $this->setGroupsOnBuilder($queryBuilder, $groups);

        return $queryBuilder;
    }

    public function paginate($filters, $relations): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'payrolls.year', 'direction' => 'DESC'],
            ['field' => 'payrolls.month', 'direction' => 'DESC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders, $relations);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function show($identifier)
    {
        $queryBuilder = $this->model::query()->where('ulid', $identifier);

        return $queryBuilder->firstOrFail();
    }
}
