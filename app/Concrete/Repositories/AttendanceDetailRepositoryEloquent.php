<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\AttendanceDetailRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\AttendanceDetail;

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

        $queryBuilder = $this->model->getQuery()
            ->join('attendances', 'attendances.id', '=', 'attendance_details.attendance_id')
            ->when($filters->attendance_ulid ?? false, function ($builder, $value) {
                $builder->where('attendances.ulid', $value);
            })
            ->select([
                "attendance_details.*",
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $queryBuilder;
    }

    public function list($filters)
    {
        $queryBuilder = $this->baseQueryBuilder($filters);

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }
}
