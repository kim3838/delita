<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\PayrollRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Payroll;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PayrollRepositoryEloquent extends BaseRepositoryEloquent implements PayrollRepository
{
    public function model(): string
    {
        return Payroll::class;
    }

    public function baseQueryBuilder($filters, $orders = []): QueryBuilder
    {
        $queryBuilder = $this->model::query()->getQuery()
            ->when(!empty($filters->company_ids) && is_array($filters->company_ids), function ($builder) use ($filters) {
                $builder->whereIn('payrolls.company_id', $filters->company_ids);
            })
            ->when(!empty($filters->payroll_ids) && is_array($filters->payroll_ids), function ($builder) use ($filters) {
                $builder->whereIn('payrolls.id', $filters->payroll_ids);
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                "payrolls.id",
                "payrolls.company_id",
                "payrolls.number",
                "payrolls.year",
                "payrolls.month",
                "payrolls.pay_frequency",
                "payrolls.frequency_sequence",
                "payrolls.start_date",
                "payrolls.end_date",
                "payrolls.remarks",
                "payrolls.status"
            ]);

        return $queryBuilder;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'payrolls.id', 'direction' => 'DESC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }
}
