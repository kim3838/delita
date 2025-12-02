<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\LeaveTypeRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\LeaveType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LeaveTypeRepositoryEloquent extends BaseRepositoryEloquent implements LeaveTypeRepository
{
    public function model(): string
    {
        return LeaveType::class;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'leave_types.code', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->model::query()->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("leave_types.company_id"), $value);
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

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }
}
