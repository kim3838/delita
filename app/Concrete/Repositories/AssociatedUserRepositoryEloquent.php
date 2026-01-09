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

class AssociatedUserRepositoryEloquent extends BaseRepositoryEloquent implements AssociatedUserRepository
{
    public function model(): string
    {
        return AssociatedUser::class;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $employeeRepositoryFilter = clone $filters;
        $employeeRepositoryFilter->search = $employeeRepositoryFilter->employee_search;
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
                    ->when(!empty($filters->status) && is_array($filters->status), function ($builder) use ($filters) {
                        $builder->whereIn('users.status', $filters->status);
                    })
                    ->when($filters->user_search ?? false, function ($builder, $value) {
                        $builder->where(function ($clause) use ($value) {
                            $clause->where('users.name', 'LIKE', ('%' . $value . '%'))
                                ->orWhere('users.email', 'LIKE', ('%' . $value . '%'));
                        });
                    })
                    ->when($filters->user_id ?? false, function ($builder, $value) {
                        $builder->orWhere('users.created_by', $value);
                    });
            })
            ->select([
                'users.id as user_id',
                'users.ulid as user_ulid',
                'users.name as user_username',
                'users.email as user_email',
                'users.status as user_status',
                'users.email_verified_at as user_email_verified_at',
                'users.timezone as user_timezone',
            ])
            ->groupBy('users.id');

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }
}
