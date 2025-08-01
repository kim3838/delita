<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\CompanyRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Company;
use Illuminate\Support\Facades\Request;

class CompanyRepositoryEloquent extends BaseRepositoryEloquent implements CompanyRepository
{
    public function model(): string
    {
        return Company::class;
    }

    public function list()
    {
        $filters = json_decode(Request::get('filters'));

        $queryBuilder = $this->model::getQuery()
            ->leftJoin('accounts', 'accounts.id', '=', 'companies.account_id')
            ->leftJoin('countries', 'countries.id', '=', 'companies.country_id')
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
            ]);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model);
    }

    public function selection()
    {
        $filters = json_decode(Request::get('filters'));

        $queryBuilder = $this->model::getQuery()
            ->select([
                'companies.id as id',
                'companies.code as code',
                'companies.name as name',
            ]);

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }

    public function show($ulid)
    {
        $queryBuilder = $this->model::where('ulid', $ulid);

        return $queryBuilder->first();
    }
}
