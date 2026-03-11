<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\UserRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Helpers\SafeExecutor;
use App\Models\Company;
use App\Models\User;
use App\Notifications\NewUserRegistered;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserRepositoryEloquent extends BaseRepositoryEloquent implements UserRepository
{
    public function model(): string
    {
        return User::class;
    }

    public function baseQueryBuilder($filters, $orders = [])
    {
        $employeeRepositoryFilter = clone $filters;
        $employeeRepositoryFilter->search = $employeeRepositoryFilter->employee_search;
        unset($employeeRepositoryFilter->user_search);
        unset($employeeRepositoryFilter->employee_search);
        unset($employeeRepositoryFilter->companies);

        $employeeQueryBuilder = App::make(EmployeeRepository::class)->baseQueryBuilder($employeeRepositoryFilter);

        $queryBuilder = $this->model::query()->getQuery()
            ->when($filters->employee_search ?? false, function ($builder) use($employeeQueryBuilder){
                $builder->joinSub($employeeQueryBuilder, 'employee_sub', function ($join) {
                    $join->on('employee_sub.user_id', '=', 'users.id');
                });
            })
            ->leftJoin('company_user', 'company_user.user_id', '=', 'users.id')
            ->whereNot('users.type', UserType::SUPER_ADMIN)
            ->when(!empty($filters->companies) && is_array($filters->companies), function ($builder) use ($filters) {
                $builder->whereIn('company_user.company_id', $filters->companies);
            })
            ->when(!empty($filters->status) && is_array($filters->status), function ($builder) use ($filters) {
                $builder->whereIn('users.status', $filters->status);
            })
            ->when($filters->user_search ?? false, function($builder, $value){
                $builder->where(function($clause) use($value){
                    $clause->where('users.name', 'LIKE', ('%' . $value . '%'))
                        ->orWhere('users.email', 'LIKE', ('%' . $value . '%'));
                });
            })
            ->select([
                'users.id',
                'users.ulid',
                'users.name',
                'users.email',
                'users.status',
                'users.email_verified_at',
                'users.timezone',
                'users.employable',
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

            /**
             * If filtered with accounts, show only user with roles in account_ids or no roles assigned
             **/
            ->when(!empty($filters->account_ids) && is_array($filters->account_ids), function ($builder) use ($filters) {
                $builder->whereIn('roles.account_id', $filters->account_ids)->orWhereNull('roles.account_id');
            })
            ->select([
                'user_sub.id as id',
                'user_sub.ulid as ulid',
                'user_sub.name as username',
                'user_sub.email as email',
                'user_sub.status as status',
                'user_sub.email_verified_at as email_verified_at',
                'user_sub.timezone as timezone',
                'user_sub.employable as employable',
                'user_sub.created_by as created_by',
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

    public function store($attributes)
    {
        $user = $this->model::query()->create($attributes);

        SafeExecutor::try(function () use ($user, $attributes) {

            $user->notify(new NewUserRegistered($attributes['name'], $attributes['email'], $attributes['pre_hash_password']));
        });

        return $user;
    }

    public function show($identifier)
    {
        $queryBuilder = $this->model::query()->where('ulid', $identifier);

        return $queryBuilder->firstOrFail();
    }

    public function autoGenerate($data)
    {
        $companyId = $data['company_id'];
        $officeEmail = $data['office_email'];
        $employable = $data['employable'];

        $companyTimezone = Company::query()->find($companyId)->timezone;

        $username = $this->generateUsername($data);
        $password = Str::random(8);

        return $this->store([
            'name' => $username,
            'email' => $officeEmail,
            'pre_hash_password' => $password,
            'password' => Hash::make($password),
            'status' => UserStatus::ACTIVE->value,
            'timezone' => $companyTimezone,
            'employable' => $employable,
        ]);
    }

    public function generateUsername(array $data): string
    {
        $familyName = strtolower(preg_replace('/\s+/', '', $data['family_name']));
        $givenName = strtolower(preg_replace('/\s+/', '', $data['given_name']));
        $givenNameFirstCharacter = substr($givenName, 0, 1);

        do {
            $userCount = User::query()->count();
            $randomPart = substr((string) crc32($userCount + rand(1, 9999)), -3) . Str::lower(Str::random(1));
            $username = "{$familyName}.{$givenNameFirstCharacter}.{$randomPart}";

            $exists = User::query()->where('name', $username)->exists();

        } while ($exists);

        return $username;
    }
}
