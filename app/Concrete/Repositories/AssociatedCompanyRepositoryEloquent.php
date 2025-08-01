<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\AssociatedCompanyRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\CompanyUser;
use App\Models\Hydrations\AssociatedCompany;
use Illuminate\Support\Facades\Request;

class AssociatedCompanyRepositoryEloquent extends BaseRepositoryEloquent implements AssociatedCompanyRepository
{
    public function model(): string
    {
        return AssociatedCompany::class;
    }

    public function list()
    {
        $filters = json_decode(Request::get('filters'));

        $queryBuilder = CompanyUser::getQuery()
            ->leftJoin('companies', 'companies.id', '=', 'company_user.company_id')
            ->leftJoin('accounts', 'accounts.id', '=', 'companies.account_id')
            ->leftJoin('countries', 'countries.id', '=', 'companies.country_id')
            ->when($filters->user_id ?? false, function ($builder, $value) {
                $builder->where('company_user.user_id', $value);
            })
            ->when(!empty($filters->assignment_type) && is_array($filters->assignment_type), function ($builder) use ($filters) {
                $builder->whereIn('company_user.assignment_type', $filters->assignment_type);
            })
            ->when(!empty($filters->account_id) && is_array($filters->account_id), function ($builder) use ($filters) {
                $builder->whereIn('companies.account_id', $filters->account_id);
            })
            ->when($filters->search ?? false, function($builder, $value){
                $builder->where(function($clause) use($value){
                    $clause->where('companies.name', 'LIKE', ('%' . $value . '%'))
                        ->orWhere('accounts.number', 'LIKE', ('%' . $value . '%'))
                        ->orWhere('companies.code', 'LIKE', ('%' . $value . '%'));
                });
            })
            ->select([
                'companies.id as company_id',
                'companies.ulid as company_ulid',
                'accounts.number as account_number',
                'companies.code as company_code',
                'companies.name as company_name',
                'countries.name as country_name',
                'companies.currency as company_currency',
                'companies.timezone as company_timezone',
                'company_user.assignment_type as assignment_type',
            ]);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model);
    }

    public function selection()
    {
        $filters = json_decode(Request::get('filters'));

        $queryBuilder = CompanyUser::getQuery()
            ->leftJoin('companies', 'companies.id', '=', 'company_user.company_id')
            ->when($filters->user_id ?? false, function ($builder, $value) {
                $builder->where('company_user.user_id', $value);
            })
            ->when(!empty($filters->assignment_type) && is_array($filters->assignment_type), function ($builder) use ($filters) {
                $builder->whereIn('company_user.assignment_type', $filters->assignment_type);
            })
            ->select([
                'companies.id as company_id',
                'companies.name as company_name',
                'company_user.assignment_type as assignment_type',
            ]);

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }
}
