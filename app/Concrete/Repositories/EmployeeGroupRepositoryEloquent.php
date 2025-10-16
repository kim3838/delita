<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeeGroupRepository;
use App\Enums\GroupType;
use App\Models\Group;
use Illuminate\Support\Facades\DB;

class EmployeeGroupRepositoryEloquent extends GroupRepositoryEloquent implements EmployeeGroupRepository
{
    public function syncWithoutDetaching($employeeIds, $groupIds): void
    {
        foreach ($groupIds as $groupId) {

            $group = Group::query()->find($groupId);

            if(empty($group)){continue;}

            $group->employees()->syncWithoutDetaching($employeeIds);
        }
    }

    public function detachAssignedGroups($groupIds): void
    {
        foreach ($groupIds as $groupId) {

            $group = Group::query()->find($groupId);

            if(empty($group)){continue;}

            $group->employees()->detach();
        }
    }

    public function selection($filters)
    {
        $queryBuilder = $this->model->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("groups.company_id"), $value);
            })
            ->where("groups.type", GroupType::EMPLOYEE)
            ->when($filters->search ?? false, function($builder, $value){
                $builder->where(function($clause) use($value){
                    $clause->where('groups.name', 'LIKE', "%$value%");
                });
            })
            ->select([
                'groups.id AS id',
                'groups.name AS name',
            ])
            ->orderBy('name', 'ASC');

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }
}
