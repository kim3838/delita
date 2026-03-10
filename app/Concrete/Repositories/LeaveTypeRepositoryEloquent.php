<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\LeaveTypeRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\LeaveType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LeaveTypeRepositoryEloquent extends BaseRepositoryEloquent implements LeaveTypeRepository
{
    public function model(): string
    {
        return LeaveType::class;
    }

    public function baseQueryBuilder($filters, $orders = [])
    {
        $queryBuilder = $this->model::query()->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("leave_types.company_id"), $value);
            })
            ->when(!empty($filters->ids) && is_array($filters->ids), function ($builder) use ($filters) {
                $builder->whereIn('leave_types.id', $filters->ids);
            })
            ->when(!empty($filters->type) && is_array($filters->type), function ($builder) use ($filters) {
                $builder->whereIn('leave_types.type', $filters->type);
            })
            ->when($filters->search ?? false, function($builder, $value){
                $builder->where(function($clause) use($value){
                    $clause->where('leave_types.code', 'LIKE', ('%' . $value . '%'))
                        ->orWhere('leave_types.name', 'LIKE', ('%' . $value . '%'));
                });
            })
            ->when(!empty($filters->type) && is_array($filters->type), function ($builder) use ($filters) {
                $builder->whereIn('leave_types.type', $filters->type);
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(" . $this->rowNumberOrder($orders) . ") AS `row_number`"),
                'leave_types.*',
            ]);

        return $queryBuilder;

    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'leave_types.code', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function list($filters): Collection
    {
        $orders = [
            ['field' => 'leave_types.code', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }

    public function selection($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'leave_types.code', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->model::query()->getQuery()
            ->when(property_exists($filters, 'company_id'), function ($builder) use($filters){
                $builder->where('leave_types.company_id', $filters->company_id ?? null);
            })
            ->when($filters->search ?? false, function ($builder, $value) {
                $builder->where(function ($query) use ($value) {
                    $query->where('code', 'like', "%$value%")
                        ->orWhere('name', 'like', "%$value%");
                });
            })
            ->select([
                'leave_types.id',
                'leave_types.code',
                'leave_types.name',
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

    public function update($identifier, $attributes)
    {
        $model = $this->model::query()->where('ulid', $identifier)->firstOrFail();

        $model->update($attributes);

        return $model;
    }
}
