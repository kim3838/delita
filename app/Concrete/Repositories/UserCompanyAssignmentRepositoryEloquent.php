<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\UserCompanyAssignmentRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Hydrations\User\CompanyAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserCompanyAssignmentRepositoryEloquent extends BaseRepositoryEloquent implements UserCompanyAssignmentRepository
{
    public function model(): string
    {
        return CompanyAssignment::class;
    }

    public function list($ulid, $filters)
    {
        $queryBuilder = User::getQuery()
            ->where('users.ulid', $ulid)
            ->crossJoin('companies')
            ->when(!empty($filters->associated_companies) && is_array($filters->associated_companies), function ($builder) use ($filters) {
                $builder->whereIn('companies.id', $filters->associated_companies);
            })
            ->leftJoin('company_user', function($join){
                $join->on(DB::raw("company_user.user_id"), '=', DB::raw("users.id"))
                    ->where(DB::raw("company_user.company_id"), '=', DB::raw("companies.id"));
            })
            ->leftJoin('employees', function($join){
                $join->on(DB::raw("employees.user_id"), '=', DB::raw("users.id"))
                    ->where(DB::raw("employees.company_id"), '=', DB::raw("companies.id"));
            })
            ->select([
                'users.id AS user_id',
                'companies.id AS company_id',
                'companies.code AS company_code',
                'companies.name AS company_name',
                'company_user.assignment_type AS company_assignment_type',
                'employees.id AS employee_id',
                'employees.number AS employee_number',
                'employees.family_name AS employee_family_name',
                'employees.middle_name AS employee_middle_name',
                'employees.given_name AS employee_given_name',
            ]);

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }
}
