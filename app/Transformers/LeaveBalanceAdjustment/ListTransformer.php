<?php

namespace App\Transformers\LeaveBalanceAdjustment;

use App\Facades\Fractal;
use App\Models\LeaveBalanceAdjustment;
use App\Models\LeaveType;
use App\Transformers\LeaveType\ItemTransformer as LeaveTypeItemTransformer;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(LeaveBalanceAdjustment $leaveBalanceAdjustment): array
    {
        $leaveType = LeaveType::query()->find($leaveBalanceAdjustment->leave_type_id);

        $leaveType = $leaveType ? Fractal::item($leaveType, LeaveTypeItemTransformer::class) : $leaveType;

        return [
            'row_number' => $leaveBalanceAdjustment->row_number,
            'id' => $leaveBalanceAdjustment->id,
            'ulid' => $leaveBalanceAdjustment->ulid,
            'employee_id' => $leaveBalanceAdjustment->employee_id,
            'leave_type_id' => $leaveBalanceAdjustment->leave_type_id,
            'type' => $leaveBalanceAdjustment->type?->toArray(),
            'balance' => $leaveBalanceAdjustment->balance,
            'remarks' => $leaveBalanceAdjustment->remarks,
            'effective_date' => $leaveBalanceAdjustment->effective_date->toDateString(),
            'effective_date_readable' => $leaveBalanceAdjustment->effective_date->format('M j, Y'),
            'employee' => [
                'number' => $leaveBalanceAdjustment->employee_number,
                'full_name' => $leaveBalanceAdjustment->employee_full_name,
            ],
            'leave_type' => $leaveType
        ];
    }
}
