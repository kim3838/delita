<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\GroupRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Group;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class GroupRepositoryEloquent extends BaseRepositoryEloquent implements GroupRepository
{
    public function model(): string
    {
        return Group::class;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'groups.name', 'direction' => 'ASC'],
        ];

        $groups = [
            'groups.id'
        ];

        $queryBuilder = $this->model::query()->getQuery()
            ->leftJoin('groupables', 'groupables.group_id', '=', 'groups.id')
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("groups.company_id"), $value);
            })
            ->when(!empty($filters->type) && is_array($filters->type), function ($builder) use ($filters) {
                $builder->whereIn("groups.type", $filters->type);
            })
            ->when($filters->search ?? false, function($builder, $value){
                $builder->where(function($clause) use($value){
                    $clause->where('groups.name', 'LIKE', "%$value%");
                });
            })
            ->select([
                'groups.*',
                DB::raw("COUNT(groupables.group_id) AS `groupables_count`"),
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);
        $this->setGroupsOnBuilder($queryBuilder, $groups);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function update($identifier, $attributes)
    {
        $model = $this->model::query()->where('ulid', $identifier)->firstOrFail();

        $model->update($attributes);

        return $model;
    }
}
