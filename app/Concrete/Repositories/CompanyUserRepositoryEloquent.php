<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\CompanyUserRepository;
use App\Blueprint\Repositories\EmployeeRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\UserType;
use App\Models\Hydrations\CompanyUser;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class CompanyUserRepositoryEloquent extends BaseRepositoryEloquent implements CompanyUserRepository
{
    public function model(): string
    {
        return CompanyUser::class;
    }

    public function baseQueryBuilder($filters, $orders = [])
    {
        $employeeRepositoryFilter = clone $filters;
        if(isset($employeeRepositoryFilter->employee_search)){
            $employeeRepositoryFilter->search = $employeeRepositoryFilter->employee_search;
        }
        unset($employeeRepositoryFilter->user_search);
        unset($employeeRepositoryFilter->employee_search);
        unset($employeeRepositoryFilter->associated_companies);

        $employeeQueryBuilder = App::make(EmployeeRepository::class)->baseQueryBuilder($employeeRepositoryFilter);

        $queryBuilder = User::query()->getQuery()
            ->when($filters->employee_search ?? false, function ($builder) use($employeeQueryBuilder){
                $builder->joinSub($employeeQueryBuilder, 'employee_sub', function ($join) {
                    $join->on('employee_sub.user_id', '=', 'users.id');
                });
            })
            ->leftJoin('company_user', 'company_user.user_id', '=', 'users.id')
            ->whereNot('users.type', UserType::SUPER_ADMIN)
            ->where(function ($clause) use ($filters) {
                $clause->whereIn('company_user.company_id', $filters->associated_companies);
            })
            ->when(!empty($filters->status) && is_array($filters->status), function ($builder) use ($filters) {
                $builder->whereIn('users.status', $filters->status);
            })
            ->when($filters->search ?? false, function ($builder, $value) {
                $builder->where(function ($clause) use ($value) {
                    $clause->where('users.name', 'LIKE', ('%' . $value . '%'))
                        ->orWhere('users.email', 'LIKE', ('%' . $value . '%'));
                });
            })
            ->when($filters->user_search ?? false, function ($builder, $value) {
                $builder->where(function ($clause) use ($value) {
                    $clause->where('users.name', 'LIKE', ('%' . $value . '%'))
                        ->orWhere('users.email', 'LIKE', ('%' . $value . '%'));
                });
            })
            ->when(!empty($filters->pre_selected_user_ids) && is_array($filters->pre_selected_user_ids), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("users.id"), $filters->pre_selected_user_ids);
            })
            ->select([
                'users.id',
                'users.ulid',
                'users.name',
                'users.email',
                'users.status',
                'users.email_verified_at',
                'users.timezone',
                'users.created_by',
                DB::raw("MIN(`company_user`.`company_id`) AS `company_id`")
            ]);

        $this->setGroupsOnBuilder($queryBuilder, ['users.id']);

        /**
         * Filter user by role account_id
         **/
        $queryBuilder = $this->queryAsSub($queryBuilder, 'user_sub')
            ->leftJoin('model_has_roles', function ($join) {
                $join->on(DB::raw("model_has_roles.model_id"), '=', DB::raw("user_sub.id"))
                    ->where(DB::raw("model_has_roles.model_type"), '=', 'user');
            })
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where(function ($clause) use ($filters) {
                /**
                 * If filtered with accounts, show only user with roles in account_ids or no roles assigned
                 **/
                $clause->where('roles.account_id', $filters->account_id)->orWhereNull('roles.account_id');
            })
            ->select([
                'user_sub.id as user_id',
                'user_sub.ulid as user_ulid',
                'user_sub.name as user_username',
                'user_sub.email as user_email',
                'user_sub.status as user_status',
                'user_sub.email_verified_at as user_email_verified_at',
                'user_sub.timezone as user_timezone',
                'user_sub.company_id as company_id'
            ]);

        $this->setGroupsOnBuilder($queryBuilder, ['user_sub.id']);

        /**
         * Get company info
         **/
        $queryBuilder = $this->queryAsSub($queryBuilder, 'user_sub')
            ->leftJoin('companies', 'companies.id', '=', 'user_sub.company_id')
            ->leftJoin('company_user', function ($join) {
                $join->on('company_user.user_id', '=', 'user_sub.user_id')
                    ->where('company_user.company_id', '=', DB::raw("user_sub.company_id"));
            })
            ->leftJoin('employees', function ($join) {
                $join->on('employees.user_id', '=', 'user_sub.user_id')
                    ->where('employees.company_id', '=', DB::raw("user_sub.company_id"));
            })
            ->select([
                'user_sub.*',
                'companies.name AS company_name',
                'companies.timezone AS company_timezone',
                'company_user.assignment_type AS company_assignment_type',
                DB::raw("employees.id IS NOT NULL AS is_employee"),
                'employees.number AS company_employee_number',
                'employees.family_name AS company_employee_family_name',
                'employees.middle_name AS company_employee_middle_name',
                'employees.given_name AS company_employee_given_name',
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $queryBuilder;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'user_sub.user_id', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function list($filters): Collection
    {
        $orders = [
            ['field' => 'user_sub.user_id', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }

    public function selection($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'user_sub.user_username', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }
}
