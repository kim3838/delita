<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\AssociatedCompanyRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Hydrations\AssociatedCompany;
use Illuminate\Support\Facades\Request;

class AssociatedCompanyRepositoryEloquent extends BaseRepositoryEloquent implements AssociatedCompanyRepository
{
    public function model(): string
    {
        return AssociatedCompany::class;
    }

    public function selection()
    {
        $filters = json_decode(Request::get('filters'));

        $queryBuilder = CompanyUser::getQuery()
            ->leftJoin('users', 'users.id', '=', 'company_user.user_id')
            ->leftJoin('companies', 'companies.id', '=', 'company_user.company_id')
            ->when($filters->user_id ?? false, function ($builder, $value) {
                $builder->where('users.id', $value);
            })
            ->select([
                'companies.id as company_id',
                'companies.name as company_name',
                'company_user.assignment_type',
            ]);

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }
}
