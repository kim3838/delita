<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\JsonPresetRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\JsonPreset;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JsonPresetRepositoryEloquent extends BaseRepositoryEloquent implements JsonPresetRepository
{
    public function model(): string
    {
        return JsonPreset::class;
    }

    public function list($filters)
    {
        $queryBuilder = $this->model->getQuery()
            ->when($filters->search ?? false, function($builder, $value){
                $builder->where(function($clause) use($value){
                    $clause->where('json_presets.path', 'LIKE', ('%' . $value . '%'))
                        ->orWhere('json_presets.key', 'LIKE', ('%' . $value . '%'));
                });
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER() AS row_number"),
                "json_presets.*"
            ]);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model);
    }

    public function store($attributes)
    {
        $json = $attributes['json_file'] ?? null;

        $jsonPreset = [
            'key' => $attributes['key'] ?? null,
            'disk' => 'presets',
            'path' => $attributes['path'] ?? null,
        ];

        Storage::disk($jsonPreset['disk'])->put($jsonPreset['path'], $json->get());

        return $this->model::create($jsonPreset);
    }

    public function update($id, $attributes)
    {
        $jsonPreset = $this->model::findOrfail($id);
        $jsonPreset->key = $attributes['key'];
        $jsonPreset->path = $attributes['path'];

        if($jsonPreset->isDirty('path')){

            if(isset($attributes['json_file'])){

                Storage::disk($jsonPreset->disk)->delete($jsonPreset->getOriginal('path'));

                Storage::disk($jsonPreset->disk)->put($jsonPreset->path, $attributes['json_file']->get());

            } else {

                Storage::disk($jsonPreset->disk)->move($jsonPreset->getOriginal('path'), $jsonPreset->path);
            }

        } else if (isset($attributes['json_file'])){

            Storage::disk($jsonPreset->disk)->put($jsonPreset->path, $attributes['json_file']->get());
        }

        $jsonPreset->save();

        return $jsonPreset;
    }

    public function selection($filters): LengthAwarePaginator
    {
        $queryBuilder = $this->model->getQuery()
            ->when($filters->search ?? false, function($builder, $value){
                $builder->where(function($clause) use($value){
                    $clause->where('json_presets.path', 'LIKE', ('%' . $value . '%'));
                });
            });

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model);
    }

    public function delete($id)
    {
        $jsonPreset = $this->model::findOrfail($id);

        Storage::disk($jsonPreset->disk)->delete($jsonPreset->path);

        return $jsonPreset->delete();
    }

    public function download($id)
    {
        $jsonPreset = $this->model::findOrfail($id);

        return Storage::disk($jsonPreset->disk)->download($jsonPreset->path);
    }
}
