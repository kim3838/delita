<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\ShiftRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Shift;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ShiftRepositoryEloquent extends BaseRepositoryEloquent implements ShiftRepository
{
    public function model(): string
    {
        return Shift::class;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'shifts.code', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->model::query()->getQuery()
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
                DB::raw("ROW_NUMBER() OVER(" . $this->rowNumberOrder($orders) . ") AS `row_number`"),
                'shifts.*',
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function selection($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'shifts.code', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->model::query()->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where('shifts.company_id', $value);
            })
            ->when($filters->search ?? false, function ($builder, $value) {
                $builder->where(function ($query) use ($value) {
                    $query->where('code', 'like', "%$value%")
                        ->orWhere('name', 'like', "%$value%");
                });
            })
            ->select([
                'shifts.id',
                'shifts.code',
                'shifts.name',
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function show($identifier)
    {
        $queryBuilder = $this->model::query()->where('ulid', $identifier);

        return $queryBuilder->firstOrFail();
    }
}
