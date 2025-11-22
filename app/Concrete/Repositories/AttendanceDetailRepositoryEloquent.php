<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\AttendanceDetailRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\AttendanceDetail;
use Illuminate\Support\Collection;

class AttendanceDetailRepositoryEloquent extends BaseRepositoryEloquent implements AttendanceDetailRepository
{
    public function model(): string
    {
        return AttendanceDetail::class;
    }

    public function baseQueryBuilder($filters, $orders = null)
    {
        $orders = [
            ...(!empty($orders) ? $orders : []),
            ['field' => 'attendance_details.order', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->model::query()->getQuery()
            ->join('attendances', 'attendances.id', '=', 'attendance_details.attendance_id')
            ->when($filters->attendance_ulid ?? false, function ($builder, $value) {
                $builder->where('attendances.ulid', $value);
            })
            ->when(!empty($filters->shift_breakdown_splits) && is_array($filters->shift_breakdown_splits), function ($builder) use ($filters) {
                $builder->whereIn('attendance_details.split_type', $filters->shift_breakdown_splits);
            })
            ->select([
                "attendance_details.*",
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $queryBuilder;
    }

    public function list($filters): Collection
    {
        $queryBuilder = $this->baseQueryBuilder($filters);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }
}
