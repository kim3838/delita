<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\DepartmentRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class DepartmentRepositoryEloquent extends BaseRepositoryEloquent implements DepartmentRepository
{
    public function model(): string
    {
        return Department::class;
    }

    public function list()
    {
        $filters = json_decode(Request::get('filters'));

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
            ->select([
                'departments.*'
            ])
            ->orderBy('name', 'ASC');

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }

    public function selection()
    {
        $filters = json_decode(Request::get('filters'));

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
}
