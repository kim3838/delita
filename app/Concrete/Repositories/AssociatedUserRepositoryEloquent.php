<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\AssociatedUserRepository;
use App\Blueprint\Repositories\EmployeeRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\UserType;
use App\Models\Hydrations\AssociatedUser;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class AssociatedUserRepositoryEloquent extends BaseRepositoryEloquent implements AssociatedUserRepository
{
    public function model(): string
    {
        return AssociatedUser::class;
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
                $clause->whereIn('company_user.company_id', $filters->associated_companies)
                    ->when(($filters->user_id ?? false), function ($builder) use($filters){
                        $builder->orWhere('users.created_by', $filters->user_id);
                    });
            })
            ->when(!empty($filters->status) && is_array($filters->status), function ($builder) use ($filters) {
                $builder->whereIn('users.status', $filters->status);
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
            ]);

        $this->setGroupsOnBuilder($queryBuilder, ['users.id']);

        /**
         * User roles
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
            ->when($filters->user_id ?? false, function ($builder, $value) {
                $builder->orWhere('user_sub.created_by', $value);
            })
            ->select([
                'user_sub.id as user_id',
                'user_sub.ulid as user_ulid',
                'user_sub.name as user_username',
                'user_sub.email as user_email',
                'user_sub.status as user_status',
                'user_sub.email_verified_at as user_email_verified_at',
                'user_sub.timezone as user_timezone'
            ]);

        $this->setGroupsOnBuilder($queryBuilder, ['user_sub.id']);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $queryBuilder;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'user_sub.id', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }
}
