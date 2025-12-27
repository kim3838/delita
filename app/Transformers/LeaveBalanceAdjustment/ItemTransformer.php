<?php

namespace App\Transformers\LeaveBalanceAdjustment;

use App\Facades\Fractal;
use App\Models\LeaveBalanceAdjustment;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(LeaveBalanceAdjustment $leaveBalanceAdjustment): array
    {
        return [
            ...Fractal::item($leaveBalanceAdjustment, ListTransformer::class)
        ];
    }
}
