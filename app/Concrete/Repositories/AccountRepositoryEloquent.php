<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\AccountRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Account;

class AccountRepositoryEloquent extends BaseRepositoryEloquent implements AccountRepository
{
    public function model(): string
    {
        return Account::class;
    }

    public function list($filters)
    {
        $queryBuilder = $this->model::getQuery()
            ->when(!empty($filters->account_type) && is_array($filters->account_type), function ($builder) use ($filters) {
                $builder->whereIn('accounts.type', $filters->account_type);
            })
            ->select([
                'accounts.id as id',
                'accounts.ulid as ulid',
                'accounts.number as number',
                'accounts.type as type',
                'accounts.date_registered as date_registered',
            ]);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model);
    }

    public function selection($filters)
    {
        $queryBuilder = $this->model::getQuery()->select(['accounts.*']);

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }

    public function show($ulid)
    {
        $queryBuilder = $this->model::where('ulid', $ulid);

        return $queryBuilder->first();
    }
}
