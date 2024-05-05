<?php

namespace App\Concrete\Repositories;

use App\Concrete\BaseRepositoryEloquent;
use App\Models\Prototype;
use Illuminate\Support\Facades\Request;

class PrototypeRepositoryEloquent extends BaseRepositoryEloquent
{
    public function model(): string
    {
        return Prototype::class;
    }

    public function selection()
    {
        $filters = json_decode(Request::get('filters'));

        $queryBuilder = $this->model->getQuery()
            ->orderBy('name', 'ASC')
            ->when($filters->id ?? false, function ($builder, $value) {
                if(is_array($value)){
                    $builder->whereIn('id', $value);
                } else {
                    $builder->where('id', $value);
                }
            })
            ->when($filters->search ?? false, function ($builder, $value) {
                $builder->where(function ($query) use ($value) {
                    $query->where('name', 'like', '%' . $value . '%')->orWhere('code', 'like', '%' . $value . '%');
                });
            });

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model);
    }
}
