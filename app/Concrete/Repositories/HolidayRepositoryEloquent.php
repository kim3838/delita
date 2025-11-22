<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\HolidayRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Holiday;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class HolidayRepositoryEloquent extends BaseRepositoryEloquent implements HolidayRepository
{
    public function model(): string
    {
        return Holiday::class;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'date', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->model::query()->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where('company_id', $value);
            })
            ->when($filters->search ?? false, function($builder, $value){
                $builder->where(function($clause) use($value){
                    $clause->where('holidays.name', 'LIKE', "%$value%");
                });
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                'holidays.*',
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function selection($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'holidays.name', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->model::query()->getQuery()
            ->when(!empty($filters->id) && is_array($filters->id), function ($builder) use ($filters) {
                $builder->whereIn('holidays.id', $filters->id);
            })
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where('holidays.company_id', $value);
            })
            ->when($filters->search ?? false, function ($builder, $value) {
                $builder->where(function ($query) use ($value) {
                    $query->where('name', 'like', "%$value%");
                });
            })
            ->select([
                'holidays.id',
                'holidays.name'
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function update($identifier, $attributes)
    {
        $holiday = $this->model::query()->where('ulid', $identifier)->firstOrFail();

        $holiday->update($attributes);

        return $holiday;
    }
}
