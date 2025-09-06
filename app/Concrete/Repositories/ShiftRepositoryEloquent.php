<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\ShiftRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;

class ShiftRepositoryEloquent extends BaseRepositoryEloquent implements ShiftRepository
{
    public function model(): string
    {
        return Shift::class;
    }

    public function list($filters)
    {
        $queryBuilder = $this->model::getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("shifts.company_id"), $value);
            })
            ->when($filters->search ?? false, function($builder, $value){
                $builder->where(function($clause) use($value){
                    $clause->where('shifts.code', 'LIKE', ('%' . $value . '%'))
                        ->orWhere('shifts.name', 'LIKE', ('%' . $value . '%'));
                });
            })
            ->when(!empty($filters->type) && is_array($filters->type), function ($builder) use ($filters) {
                $builder->whereIn('shifts.type', $filters->type);
            })
            ->select([
                'shifts.*',
            ]);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model);
    }

    public function show($ulid)
    {
        $queryBuilder = $this->model::where('ulid', $ulid);

        return $queryBuilder->firstOrFail();
    }
}
