<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\LeaveTypeBalancePerPeriodRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\LeaveTypeBalancePerPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LeaveTypeBalancePerPeriodRepositoryEloquent extends BaseRepositoryEloquent implements LeaveTypeBalancePerPeriodRepository
{
    public function model(): string
    {
        return LeaveTypeBalancePerPeriod::class;
    }

    public function list($filters): Collection
    {
        $queryBuilder = $this->model::query()->getQuery()
            ->when($filters->leave_type_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("leave_type_balance_per_periods.leave_type_id"), $value);
            })
            ->orderBy('from_period', 'ASC')
            ->orderBy('to_period', 'ASC')
            ->select([
                'leave_type_balance_per_periods.*',
            ]);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }
}
