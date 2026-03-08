<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\UserCompanyAssignmentRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Hydrations\User\CompanyAssignment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserCompanyAssignmentRepositoryEloquent extends BaseRepositoryEloquent implements UserCompanyAssignmentRepository
{
    public function model(): string
    {
        return CompanyAssignment::class;
    }

    public function list($filters): Collection
    {
        $queryBuilder = User::query()->getQuery()
            ->when($filters->user_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("users.id"), $value);
            })
            ->when($filters->user_ulid ?? false, function ($builder, $value) {
                $builder->where(DB::raw("users.ulid"), $value);
            })
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
                'company_user.id AS company_user_id',
                'users.id AS user_id',
                'companies.id AS company_id',
                'companies.code AS company_code',
                'companies.name AS company_name',
                'company_user.assignment_type AS company_assignment_type',
                'employees.id AS employee_id',
                'employees.number AS employee_number',
                DB::raw("CONCAT_WS(' ',employees.family_name,employees.given_name,employees.middle_name) AS employee_full_name"),
            ]);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }

    public function sync($userId, $companyAssignments): array
    {
        return User::query()->findOrFail($userId)->companies()->sync($companyAssignments);
    }
}
