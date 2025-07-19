<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\AssociatedAccountRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\CompanyUser;
use App\Models\Hydrations\AssociatedAccount;
use Illuminate\Support\Facades\Request;

class AssociatedAccountRepositoryEloquent extends BaseRepositoryEloquent implements AssociatedAccountRepository
{
    public function model(): string
    {
        return AssociatedAccount::class;
    }

    public function list()
    {
        $filters = json_decode(Request::get('filters'));

        $queryBuilder = CompanyUser::getQuery()
            ->leftJoin('companies', 'companies.id', '=', 'company_user.company_id')
            ->leftJoin('accounts', 'accounts.id', '=', 'companies.account_id')
            ->when($filters->user_id ?? false, function ($builder, $value) {
                $builder->where('company_user.user_id', $value);
            })
            ->when(!empty($filters->assignment_type) && is_array($filters->assignment_type), function ($builder) use ($filters) {
                $builder->whereIn('company_user.assignment_type', $filters->assignment_type);
            })
            ->when(!empty($filters->account_type) && is_array($filters->account_type), function ($builder) use ($filters) {
                $builder->whereIn('accounts.type', $filters->account_type);
            })
            ->select([
                'accounts.id as account_id',
                'accounts.ulid as account_ulid',
                'accounts.number as account_number',
                'accounts.type as account_type',
                'accounts.date_registered as account_date_registered',
            ])
            ->groupBy('accounts.id');

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model);
    }

    public function selection()
    {
        $filters = json_decode(Request::get('filters'));

        $queryBuilder = CompanyUser::getQuery()
            ->leftJoin('companies', 'companies.id', '=', 'company_user.company_id')
            ->leftJoin('accounts', 'accounts.id', '=', 'companies.account_id')
            ->when($filters->user_id ?? false, function ($builder, $value) {
                $builder->where('company_user.user_id', $value);
            })
            ->when(!empty($filters->assignment_type) && is_array($filters->assignment_type), function ($builder) use ($filters) {
                $builder->whereIn('company_user.assignment_type', $filters->assignment_type);
            })
            ->select([
                'accounts.id as account_id',
                'accounts.number as account_number',
            ])
            ->groupBy('accounts.id');

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }
}
