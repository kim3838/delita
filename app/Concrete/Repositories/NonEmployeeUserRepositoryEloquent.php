<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\NonEmployeeUserRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Hydrations\NonEmployeeUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class NonEmployeeUserRepositoryEloquent extends BaseRepositoryEloquent implements NonEmployeeUserRepository
{
    public function model(): string
    {
        return NonEmployeeUser::class;
    }

    public function selection()
    {
        $filters = json_decode(Request::get('filters'));

        $queryBuilder = User::getQuery()
            ->join('company_user', 'company_user.user_id', '=', 'users.id')
            ->leftJoin('employees', function($join){
                $join->on(DB::raw("employees.user_id"), '=', DB::raw("users.id"))
                    ->where(DB::raw("employees.company_id"), '=', DB::raw("company_user.company_id"));
            })
            ->when(!empty($filters->status) && is_array($filters->status), function ($builder) use ($filters) {
                $builder->whereIn('users.status', $filters->status);
            })
            ->when(!empty($filters->companies) && is_array($filters->companies), function ($builder) use ($filters) {
                $builder->whereIn('company_user.company_id', $filters->companies);
            })
            ->whereNull('employees.user_id')
            ->select([
                'users.id',
                'users.name',
                'users.email'
            ]);

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }
}
