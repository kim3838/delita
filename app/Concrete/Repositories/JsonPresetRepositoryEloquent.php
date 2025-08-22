<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\JsonPresetRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\JsonPreset;
use Illuminate\Pagination\LengthAwarePaginator;

class JsonPresetRepositoryEloquent extends BaseRepositoryEloquent implements JsonPresetRepository
{
    public function model(): string
    {
        return JsonPreset::class;
    }

    public function selection($filters): LengthAwarePaginator
    {
        $queryBuilder = $this->model->getQuery()
            ->when($filters->search ?? false, function($builder, $value){
                $builder->where(function($clause) use($value){
                    $clause->where('json_presets.resource_path', 'LIKE', ('%' . $value . '%'));
                });
            });

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model);
    }
}
