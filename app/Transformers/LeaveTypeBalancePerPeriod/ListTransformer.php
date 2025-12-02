<?php

namespace App\Transformers\LeaveTypeBalancePerPeriod;

use App\Models\Department;
use App\Models\LeaveTypeBalancePerPeriod;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(LeaveTypeBalancePerPeriod $model): array
    {
        return [
            'id' => $model->id,
            'leave_type_id' => $model->leave_type_id,
            'from_period' => $model->from_period,
            'and_so_on' => $model->to_period == null ? true : false,
            'to_period' => $model->to_period,
            'balance' => $model->balance,
        ];
    }
}
