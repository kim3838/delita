<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\PayrollRepository;
use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\PayrollStatus;
use App\Models\Hydrations\PayrollTotals;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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
            ->leftJoin('companies', 'companies.id', '=', 'payrolls.company_id')
            ->when(in_array('salary_statement', $relations), function ($builder) use($filters) {

                $salaryStatementRepositoryFilter = clone $filters;
                unset($salaryStatementRepositoryFilter->search);

                $salaryStatementQueryBuilder = App::make(SalaryStatementRepository::class)->baseQueryBuilder($salaryStatementRepositoryFilter, [], ['detail_totals']);

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
            ->when(!empty($filters->payroll_ulids) && is_array($filters->payroll_ulids), function ($builder) use ($filters) {
                $builder->whereIn('payrolls.ulid', $filters->payroll_ulids);
            })
            ->when(!empty($filters->payroll_not_in_statuses) && is_array($filters->payroll_not_in_statuses), function ($builder) use ($filters) {
                $builder->whereNotIn('payrolls.status', $filters->payroll_not_in_statuses);
            })
            ->when(isset($filters->year), function ($builder) use ($filters) {
                $builder->where('payrolls.year', $filters->year);
            })
            ->when(isset($filters->month), function ($builder) use ($filters) {
                $builder->where('payrolls.month', $filters->month);
            })
            ->when(isset($filters->is_after_start_date), function ($builder) use ($filters) {
                $builder->where('payrolls.start_date', '<', $filters->is_after_start_date);
            })
            ->when(isset($filters->pay_frequency), function ($builder) use ($filters) {
                $builder->where('payrolls.pay_frequency', $filters->pay_frequency);
            })
            ->when(isset($filters->frequency_sequence), function ($builder) use ($filters) {
                $builder->where('payrolls.frequency_sequence', $filters->frequency_sequence);
            })
            ->when(!empty($filters->pay_frequencies) && is_array($filters->pay_frequencies), function ($builder) use ($filters) {
                $builder->whereIn('payrolls.pay_frequency', $filters->pay_frequencies);
            })
            ->when(!empty($filters->frequency_sequences) && is_array($filters->frequency_sequences), function ($builder) use ($filters) {
                $builder->whereIn('payrolls.frequency_sequence', $filters->frequency_sequences);
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
                    $clause->where('payrolls.number', 'LIKE', "%$value%")
                        ->orWhere('payrolls.remarks', 'LIKE', "%$value%");
                });
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                "payrolls.id",
                "payrolls.ulid",
                "payrolls.company_id",
                "companies.currency AS company_currency_code",
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
                    DB::raw("COALESCE(SUM(salary_statement_sub.total_basic_gross), '0.000000') AS total_basic_gross"),
                    DB::raw("COALESCE(SUM(salary_statement_sub.total_other_gross), '0.000000') AS total_other_gross"),
                    DB::raw("COALESCE(SUM(salary_statement_sub.taxable), '0.000000') AS total_taxable"),
                    DB::raw("COALESCE(SUM(salary_statement_sub.nontaxable), '0.000000') AS total_nontaxable"),
                    DB::raw("COALESCE(SUM(salary_statement_sub.contribution), '0.000000') AS total_contribution"),
                    DB::raw("COALESCE(SUM(salary_statement_sub.total_employer_contribution_share), '0.000000') AS total_employer_contribution_share"),
                    DB::raw("COALESCE(SUM(salary_statement_sub.withholding_tax), '0.000000') AS total_withholding_tax"),
                    DB::raw("COALESCE(SUM(salary_statement_sub.total_tax_refund), '0.000000') AS total_tax_refund"),
                    DB::raw("COALESCE(SUM(salary_statement_sub.deduction), '0.000000') AS total_deduction"),
                    DB::raw("COALESCE(SUM(salary_statement_sub.net), '0.000000') AS total_net"),
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
            ['field' => 'payrolls.pay_frequency', 'direction' => 'ASC'],
            ['field' => 'payrolls.frequency_sequence', 'direction' => 'ASC'],
            ['field' => 'payrolls.start_date', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders, $relations);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function paginateWithTotals($filters, $relations): array
    {
        $orders = [
            ['field' => 'payrolls.year', 'direction' => 'ASC'],
            ['field' => 'payrolls.month', 'direction' => 'ASC'],
            ['field' => 'payrolls.pay_frequency', 'direction' => 'ASC'],
            ['field' => 'payrolls.frequency_sequence', 'direction' => 'ASC'],
            ['field' => 'payrolls.start_date', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders, $relations);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        /**
         * Get totals before calling createPaginationFromBuilder
         **/
        $totals = $this->queryAsSub($queryBuilder, 'payrolls_sub')
            ->select([
                "payrolls_sub.company_currency_code",
                DB::raw("SUM(payrolls_sub.total_employer_contribution_share) AS employer_contribution_share"),
                DB::raw("SUM(payrolls_sub.total_taxable) AS taxable"),
                DB::raw("SUM(payrolls_sub.total_withholding_tax) AS withholding_tax"),
                DB::raw("SUM(payrolls_sub.total_tax_refund) AS tax_refund"),
                DB::raw("SUM(payrolls_sub.total_net) AS net"),
            ])
            ->groupBy('payrolls_sub.company_currency_code');

        /**
         * Get paginator
         **/
        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return [
            $this->hydratePaginationItems($paginator, $this->model()),
            $this->hydrateCollection($totals->get(), PayrollTotals::class),
        ];
    }

    public function list($filters, $relations = []): Collection
    {
        $orders = [
            ['field' => 'payrolls.year', 'direction' => 'ASC'],
            ['field' => 'payrolls.month', 'direction' => 'ASC'],
            ['field' => 'payrolls.pay_frequency', 'direction' => 'ASC'],
            ['field' => 'payrolls.frequency_sequence', 'direction' => 'ASC'],
            ['field' => 'payrolls.start_date', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders, $relations);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }

    public function selection($filters, $relations = []): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'payrolls.year', 'direction' => 'DESC'],
            ['field' => 'payrolls.month', 'direction' => 'DESC'],
            ['field' => 'payrolls.pay_frequency', 'direction' => 'DESC'],
            ['field' => 'payrolls.frequency_sequence', 'direction' => 'DESC'],
            ['field' => 'payrolls.start_date', 'direction' => 'DESC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders, $relations);

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

    public function batchDelete($ids): int
    {
        foreach ($ids as $id) {

            $payroll = $this->model::query()->findOrfail($id);

            $deletable = $payroll->status == PayrollStatus::DRAFT;

            if($deletable){
                $this->delete($id);
            }
        }

        return 1;
    }
}
