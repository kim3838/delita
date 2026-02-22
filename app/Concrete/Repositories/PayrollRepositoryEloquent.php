<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\PayrollRepository;
use App\Blueprint\Repositories\SalaryStatementDetailRepository;
use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Payroll;
use Carbon\Carbon;
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

                $salaryStatementDetailQueryBuilder = App::make(SalaryStatementDetailRepository::class)->baseQueryBuilder($salaryStatementRepositoryFilter, [], [])
                    ->select([
                        'salary_statement_details.salary_statement_id',
                        DB::raw("component_values->>'$.employer_share.total' AS total_employer_share"),
                    ]);

                $salaryStatementDetailEmployerShareQueryBuilder = $this->queryAsSub($salaryStatementDetailQueryBuilder, 'employer_share_sub')
                    ->select([
                        'employer_share_sub.salary_statement_id',
                        DB::raw("SUM(employer_share_sub.total_employer_share) AS total_employer_contribution_share")
                    ])->groupBy('salary_statement_id');

                $builder->joinSub($salaryStatementQueryBuilder, 'salary_statement_sub', function ($join) {
                    $join->on('salary_statement_sub.payroll_id', '=', 'payrolls.id');
                })->leftJoinSub($salaryStatementDetailEmployerShareQueryBuilder, 'employer_share_sub', function ($join) {
                    $join->on('employer_share_sub.salary_statement_id', '=', 'salary_statement_sub.id');
                });
            })
            ->when(!empty($filters->company_ids) && is_array($filters->company_ids), function ($builder) use ($filters) {
                $builder->whereIn('payrolls.company_id', $filters->company_ids);
            })
            ->when(!empty($filters->payroll_ids) && is_array($filters->payroll_ids), function ($builder) use ($filters) {
                $builder->whereIn('payrolls.id', $filters->payroll_ids);
            })
            ->when(!empty($filters->payroll_ulids) && is_array($filters->payroll_ulids), function ($builder) use ($filters) {
                $builder->whereIn('payrolls.ulid', $filters->payroll_ulids);
            })
            ->when((
                (isset($filters->from_month) && Carbon::createFromFormat('Y-m', $filters->from_month)) &&
                (isset($filters->to_month) && Carbon::createFromFormat('Y-m', $filters->to_month))
            ),function($builder) use ($filters){
                $from = Carbon::createFromFormat('Y-m', $filters->from_month)->startOfMonth();
                $to = Carbon::createFromFormat('Y-m', $filters->to_month)->endOfMonth();

                $builder->whereBetween(DB::raw("DATE(CONCAT(payrolls.year, '-',LPAD(payrolls.month, 2, '0'),'-01'))"), [$from->toDateString(), $to->toDateString()]);
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
                    DB::raw("COALESCE(SUM(salary_statement_sub.net), '0.000000') AS total_salary_statement_net"),
                    DB::raw("COALESCE(SUM(employer_share_sub.total_employer_contribution_share), '0.000000') AS total_employer_contribution_share"),
                ] : []),
            ]);

        $this->setGroupsOnBuilder($queryBuilder, $groups);

        return $queryBuilder;
    }

    public function paginate($filters, $relations): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'payrolls.year', 'direction' => 'ASC'],
            ['field' => 'payrolls.month', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders, $relations);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function selection($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'payrolls.id', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->model::query()->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where('payrolls.company_id', $value);
            })
            ->when($filters->search ?? false, function ($builder, $value) {
                $builder->where(function ($query) use ($value) {
                    $query->where('number', 'like', "%$value%");
                });
            })
            ->select([
                'payrolls.*',
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function show($identifier)
    {
        $filters = (object) [
            'payroll_ulids' => [$identifier]
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, [], ['salary_statement']);

        return $this->hydrateItem($queryBuilder->firstOrFail());
    }
}
