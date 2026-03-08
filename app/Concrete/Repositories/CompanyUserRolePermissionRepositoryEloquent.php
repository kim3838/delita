<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\CompanyUserRepository;
use App\Blueprint\Repositories\CompanyUserRolePermissionRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Hydrations\User\CompanyUserRolePermission;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class CompanyUserRolePermissionRepositoryEloquent extends BaseRepositoryEloquent implements CompanyUserRolePermissionRepository
{
    public function model(): string
    {
        return CompanyUserRolePermission::class;
    }

    public function baseQueryBuilder($filters, $orders = []): QueryBuilder
    {
        $companyUserRepositoryFilter = clone $filters;

        if(isset($companyUserRepositoryFilter->user_id)){
            $companyUserRepositoryFilter->pre_selected_user_ids = [$companyUserRepositoryFilter->user_id];
        }

        if(isset($companyUserRepositoryFilter->associated_company)){
            $companyUserRepositoryFilter->associated_companies = [$companyUserRepositoryFilter->associated_company];
        }

        $employeeQueryBuilder = App::make(CompanyUserRepository::class)->baseQueryBuilder($companyUserRepositoryFilter);

        $queryBuilder = $this->queryAsSub($employeeQueryBuilder, 'user_sub')
            ->whereNotNull(DB::raw("user_sub.user_id"))
            ->whereNot(DB::raw("user_sub.user_id"), 0)
            ->whereIn(DB::raw("user_sub.user_id"), [$filters->user_id ?? 0])
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereRaw(DB::raw("users.id = user_sub.user_id"));
            })
            ->leftJoin('model_has_roles', function ($join) {
                $join->on(DB::raw("model_has_roles.model_id"), '=', DB::raw("user_sub.user_id"))
                    ->where(DB::raw("model_has_roles.model_type"), '=', 'user');
            })
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.account_id', $filters->account_id)
            ->leftJoin('role_has_permissions', DB::raw("role_has_permissions.role_id"), '=', DB::raw("roles.id"))
            ->leftjoin('permissions', DB::raw("role_has_permissions.permission_id"), '=', DB::raw("permissions.id"))
            ->when(!empty($filters->permission_keys) && is_array($filters->permission_keys), function ($builder) use ($filters) {
                $builder->whereIn('permissions.name', $filters->permission_keys);
            })
            ->select([
                'user_sub.user_id',
                'user_sub.user_ulid',
                'user_sub.user_username',
                'user_sub.user_email',
                'user_sub.user_status',
                'user_sub.user_email_verified_at',
                'user_sub.user_timezone',
                'user_sub.company_id',
                'user_sub.company_name',
                'user_sub.company_assignment_type',
                'user_sub.is_employee',
                'user_sub.company_employee_number',
                'user_sub.company_employee_full_name',
                'permissions.id AS permission_id',
                'permissions.name AS permission_name',
            ]);

        $queryBuilder = Permission::getQuery()
            ->when(!empty($filters->permission_keys) && is_array($filters->permission_keys), function ($builder) use ($filters) {
                $builder->whereIn('permissions.name', $filters->permission_keys);
            })
            ->leftJoinSub($queryBuilder, 'company_user_role_permission', function ($join) {
                $join->on('company_user_role_permission.permission_name', '=', 'permissions.name');
            })
            ->select([
                'company_user_role_permission.user_id',
                'permissions.name AS permission',
                DB::raw("CASE WHEN company_user_role_permission.permission_name IS NULL THEN 0 ELSE 1 END AS permitted"),
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $queryBuilder;
    }

    public function list($filters): Collection
    {
        $queryBuilder = $this->baseQueryBuilder($filters);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }
}
