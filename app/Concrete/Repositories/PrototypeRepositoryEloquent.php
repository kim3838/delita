<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\PrototypeRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Hydrations\Prototype\DataTable as PrototypeDataTable;
use App\Models\Prototype;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class PrototypeRepositoryEloquent extends BaseRepositoryEloquent implements PrototypeRepository
{
    public function model(): string
    {
        return Prototype::class;
    }

    public function list($filters)
    {
        $tableName = $this->model->getTable();

        $queryBuilder = $this->model->getQuery()
            ->when($filters->search ?? false, function($builder, $value) use ($tableName){
                $builder->where(function($clause) use($tableName, $value){
                    $clause->where(DB::raw("$tableName.name"), 'LIKE', ('%' . $value . '%'))
                        ->orWhere("$tableName.type", 'LIKE', ('%' . $value . '%'))
                        ->orWhere("$tableName.code", 'LIKE', ('%' . $value . '%'));
                });
            })
            ->when((
                (isset($filters->datetimeFrom) && Carbon::createFromFormat('Y-m-d H:i:s', $filters->datetimeFrom)) &&
                (isset($filters->datetimeTo) && Carbon::createFromFormat('Y-m-d H:i:s', $filters->datetimeTo))
            ),function($builder) use ($filters){
                $builder->whereBetween("datetime_added", [$filters->datetimeFrom, $filters->datetimeTo]);
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER() AS `row_number`"),
                'id',
                'name',
                'code',
                'type',
                'category',
                'capacity',
                'json_data',
                'datetime_added',
                'created_at',
                'updated_at'
            ]);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, new PrototypeDataTable);
    }

    public function selection($filters)
    {
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
