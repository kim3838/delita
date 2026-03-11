<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\CompanyRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Company;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CompanyRepositoryEloquent extends BaseRepositoryEloquent implements CompanyRepository
{
    public function model(): string
    {
        return Company::class;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'companies.id', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->model::query()->getQuery()
            ->leftJoin('accounts', 'accounts.id', '=', 'companies.account_id')
            ->leftJoin('countries', 'countries.id', '=', 'companies.country_id')
            ->when(!empty($filters->account_ids) && is_array($filters->account_ids), function ($builder) use ($filters) {
                $builder->whereIn('companies.account_id', $filters->account_ids);
            })
            ->when($filters->search ?? false, function($builder, $value){
                $builder->where(function($clause) use($value){
                    $clause->where('companies.name', 'LIKE', ('%' . $value . '%'))
                        ->orWhere('companies.short_name', 'LIKE', ('%' . $value . '%'))
                        ->orWhere('accounts.number', 'LIKE', ('%' . $value . '%'))
                        ->orWhere('companies.code', 'LIKE', ('%' . $value . '%'));
                });
            })
            ->select([
                'companies.id as company_id',
                'companies.ulid as company_ulid',
                'accounts.number as account_number',
                'companies.code as company_code',
                'companies.short_name as company_short_name',
                'companies.address_line_1 as company_address_line_1',
                'companies.address_line_2 as company_address_line_2',
                'companies.name as company_name',
                'countries.name as country_name',
                'countries.iso2 as country_iso2',
                'companies.currency as company_currency',
                'companies.timezone as company_timezone',
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function selection($filters): Collection
    {
        $queryBuilder = $this->model::query()->getQuery()
            ->leftJoin('countries', 'countries.id', '=', 'companies.country_id')
            ->when(!empty($filters->account_ids) && is_array($filters->account_ids), function ($builder) use ($filters) {
                $builder->whereIn('companies.account_id', $filters->account_ids);
            })
            ->select([
                'companies.*',
                'countries.iso2 as country_iso2',
            ]);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }

    public function show($identifier)
    {
        $queryBuilder = $this->model::query()->where('ulid', $identifier);

        return $queryBuilder->firstOrFail();
    }
}
