<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\AssociatedUserRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Hydrations\AssociatedUser;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class AssociatedUserRepositoryEloquent extends BaseRepositoryEloquent implements AssociatedUserRepository
{
    public function model(): string
    {
        return AssociatedUser::class;
    }

    public function list()
    {
        $filters = json_decode(Request::get('filters'));

        $queryBuilder = User::getQuery()
            ->leftJoin('company_user', 'company_user.user_id', '=', 'users.id')
            ->when(!empty($filters->associated_companies) && is_array($filters->associated_companies), function ($builder) use ($filters) {

                $builder->where(function($clause) use($filters){
                    $clause->whereIn('company_user.company_id', $filters->associated_companies)
                        ->when($filters->user_id ?? false, function ($builder, $value) {
                            $builder->orWhere('users.created_by', $value);
                        });
                });
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

        return $this->hydratePaginationItems($paginator, $this->model);
    }
}
