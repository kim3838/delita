<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\RoleRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class RoleRepositoryEloquent extends BaseRepositoryEloquent implements RoleRepository
{
    public function model(): string
    {
        return Role::class;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $queryBuilder = $this->model::query()->getQuery()
            ->where(DB::raw("roles.account_id"), $filters->account_id)
            ->when($filters->search ?? false, function($builder, $value){
                $builder->where(function($clause) use($value){
                    $clause->where('roles.name', 'LIKE', ("%" . $value . "%"));
                });
            })
            ->select([
                'roles.id',
                'roles.ulid',
                'roles.account_id',
                'roles.name',
            ]);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function show($identifier)
    {
        $queryBuilder = $this->model::query()->where('ulid', $identifier);

        return $queryBuilder->firstOrFail();
    }

    public function update($identifier, $attributes)
    {
        $model = $this->show($identifier);

        $model->update($attributes);

        return $model;
    }

    public function permissionMap($identifier = null): array
    {
        $role = empty($identifier) ? null : $this->show($identifier);

        $map = [];

        foreach(Permission::all() as $permission){

            $map[$permission->name] = [
                'id' => $permission->id,
                'value' => empty($role) ? false : $role->hasPermissionTo($permission->name),
            ];
        }

        return $map;
    }
}
