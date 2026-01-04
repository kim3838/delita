<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\AssociatedAccountRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Account;
use App\Models\CompanyUser;
use Illuminate\Support\Collection;

class AssociatedAccountRepositoryEloquent extends BaseRepositoryEloquent implements AssociatedAccountRepository
{
    public function model(): string
    {
        return Account::class;
    }

    public function list($filters): Collection
    {
        $queryBuilder = CompanyUser::query()->getQuery()
            ->leftJoin('companies', 'companies.id', '=', 'company_user.company_id')
            ->leftJoin('accounts', 'accounts.id', '=', 'companies.account_id')
            ->when($filters->user_id ?? false, function ($builder, $value) {
                $builder->where('company_user.user_id', $value);
            })
            ->when(!empty($filters->assignment_type) && is_array($filters->assignment_type), function ($builder) use ($filters) {
                $builder->whereIn('company_user.assignment_type', $filters->assignment_type);
            })
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
            ])
            ->groupBy('accounts.id');

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }

    public function selection($filters): Collection
    {
        $queryBuilder = CompanyUser::query()->getQuery()
            ->leftJoin('companies', 'companies.id', '=', 'company_user.company_id')
            ->leftJoin('accounts', 'accounts.id', '=', 'companies.account_id')
            ->when($filters->user_id ?? false, function ($builder, $value) {
                $builder->where('company_user.user_id', $value);
            })
            ->when(!empty($filters->assignment_type) && is_array($filters->assignment_type), function ($builder) use ($filters) {
                $builder->whereIn('company_user.assignment_type', $filters->assignment_type);
            })
            ->select([
                'accounts.id as id',
                'accounts.number as number',
            ])
            ->groupBy('accounts.id');

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }
}
