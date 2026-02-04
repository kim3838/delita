<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\PayrollRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Payroll;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;

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
            });

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
