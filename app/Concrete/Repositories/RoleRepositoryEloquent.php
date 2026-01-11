<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\RoleRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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
        $orders = [
            ['field' => 'roles.id', 'direction' => 'ASC'],
        ];

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

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function selection($filters): Collection
    {
        $orders = [
            ['field' => 'roles.account_id', 'direction' => 'ASC'],
            ['field' => 'roles.id', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->model::query()->getQuery()
            ->when(!empty($filters->account_ids) && is_array($filters->account_ids), function ($builder) use ($filters) {
                $builder->whereIn('roles.account_id', $filters->account_ids);
            })
            ->select([
                'roles.*',
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
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
                'value' => empty($role) ? false : $role->permissions->contains('name', $permission->name),
            ];
        }

        return $map;
    }
}
