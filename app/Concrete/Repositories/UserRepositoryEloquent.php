<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\UserRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Helpers\SafeExecutor;
use App\Models\Company;
use App\Models\User;
use App\Notifications\NewUserRegistered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserRepositoryEloquent extends BaseRepositoryEloquent implements UserRepository
{
    public function model(): string
    {
        return User::class;
    }

    public function list($filters)
    {
        $queryBuilder = $this->model::getQuery()
            ->leftJoin('company_user', 'company_user.user_id', '=', 'users.id')
            ->whereNot('users.type', UserType::SUPER_ADMIN)
            ->when(!empty($filters->companies) && is_array($filters->companies), function ($builder) use ($filters) {
                $builder->whereIn('company_user.company_id', $filters->companies);
            })
            ->when(!empty($filters->status) && is_array($filters->status), function ($builder) use ($filters) {
                $builder->whereIn('users.status', $filters->status);
            })
            ->when($filters->search ?? false, function($builder, $value){
                $builder->where(function($clause) use($value){
                    $clause->where('users.name', 'LIKE', ('%' . $value . '%'))
                        ->orWhere('users.email', 'LIKE', ('%' . $value . '%'));
                });
            })
            ->select([
                'users.id as id',
                'users.ulid as ulid',
                'users.name as username',
                'users.email as email',
                'users.status as status',
                'users.email_verified_at as email_verified_at',
                'users.timezone as timezone',
            ])
            ->groupBy('users.id');

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model);
    }

    public function store($attributes)
    {
        $user = $this->model::create($attributes);

        SafeExecutor::try(function () use ($user, $attributes) {

            $user->notify(new NewUserRegistered($attributes['name'], $attributes['email'], $attributes['pre_hash_password']));
        });

        return $user;
    }

    public function show($ulid)
    {
        $queryBuilder = $this->model::where('ulid', $ulid);

        return $queryBuilder->first();
    }

    public function autoGenerate($data)
    {
        $companyId = $data['company_id'];
        $officeEmail = $data['office_email'];
        $familyName = preg_replace('/\s+/', '', $data['family_name']);
        $givenName = preg_replace('/\s+/', '', $data['given_name']);

        $userCount = (int)User::count();
        $companyTimezone = Company::find($companyId)->timezone;

        $familyName = strtolower($familyName);
        $givenNameFirstCharacter = substr(strtolower($givenName), 0, 1);
        $username = "$familyName.$givenNameFirstCharacter." . ($userCount+1);
        $password = Str::random(8);

        return $this->store([
            'name' => $username,
            'email' => $officeEmail,
            'pre_hash_password' => $password,
            'password' => Hash::make($password),
            'status' => UserStatus::ACTIVE->value,
            'timezone' => $companyTimezone,
        ]);
    }
}
