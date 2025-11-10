<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\DepartmentRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class DepartmentRepositoryEloquent extends BaseRepositoryEloquent implements DepartmentRepository
{
    public function model(): string
    {
        return Department::class;
    }

    public function list($filters)
    {
        $queryBuilder = $this->model->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("departments.company_id"), $value);
            })
            ->when($filters->search ?? false, function($builder, $value){
                $builder->where(function($clause) use($value){
                    $clause->where('departments.name', 'LIKE', ('%' . $value . '%'));
                });
            })
            ->when(isset($filters->is_parent), function ($builder) use($filters){

                if($filters->is_parent){
                    $builder->whereNull("departments.parent_id");
                } else {
                    $builder->whereNotNull("departments.parent_id");
                }
            })
            ->when($filters->search ?? false, function($builder, $value) use($filters) {
                $builder->orWhere(function($builder) use($value, $filters){
                    $builder->whereIn('departments.id', function($query) use($value, $filters){
                        $query->select('parent_id')
                            ->from('departments')
                            ->where(DB::raw("departments.company_id"), $filters->company_id)
                            ->whereNotNull(DB::raw("departments.parent_id"))
                            ->where(DB::raw("departments.name"), 'LIKE', ('%' . $value . '%'));
                    });
                });
            })
            ->select([
                'departments.*'
            ])
            ->orderBy('name', 'ASC');

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }

    public function selection($filters)
    {
        $queryBuilder = $this->model->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("departments.company_id"), $value);
            })
            ->when(isset($filters->is_parent), function ($builder) use($filters){

                if($filters->is_parent){
                    $builder->whereNull("departments.parent_id");
                } else {
                    $builder->whereNotNull("departments.parent_id");
                }
            })
            ->when(!empty($filters->except), function ($builder) use($filters){
                $builder->whereNotIn('departments.id', $filters->except);
            })
            ->select([
                'departments.id AS id',
                'departments.name AS name',
            ])
            ->orderBy('name', 'ASC');

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }

    public function update($id, $attributes)
    {
        $model = $this->model::findOrfail($id);

        $model->update($attributes);

        if(!empty($model->parent_id)){

            $this->model::where('parent_id', $model->id)->update(['parent_id' => null]);
        }

        return $model;
    }
}
