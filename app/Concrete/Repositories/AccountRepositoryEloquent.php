<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\AccountRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Events\Repositories\AccountCreated;
use App\Models\Account;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AccountRepositoryEloquent extends BaseRepositoryEloquent implements AccountRepository
{
    public function model(): string
    {
        return Account::class;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $queryBuilder = $this->model::query()->getQuery()
            ->when($filters->search ?? false, function ($builder, $value) {
                $builder->where(function ($clause) use ($value) {
                    $clause->where('accounts.number', 'LIKE', "%$value%")
                        ->orWhere('accounts.email', 'LIKE', "%$value%");
                });
            })
            ->select([
                'accounts.id as id',
                'accounts.ulid as ulid',
                'accounts.number as number',
                'accounts.email as email',
                'accounts.date_registered as date_registered',
            ]);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function selection($filters): Collection
    {
        $queryBuilder = $this->model::query()->getQuery()->select(['accounts.*']);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }

    public function show($identifier)
    {
        $queryBuilder = $this->model::query()->where('ulid', $identifier);

        return $queryBuilder->firstOrFail();
    }
}
