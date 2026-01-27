<?php

namespace App\Transformers\LeaveRequest;

use App\Models\LeaveRequest;
use League\Fractal\TransformerAbstract;

class PatchableTransformer extends TransformerAbstract
{
    public function transform(LeaveRequest $leaveRequest): array
    {
        return [
            'company_id' => $leaveRequest->company_id,
            'employee_id' => $leaveRequest->employee_id,
            'shift_id' => $leaveRequest->shift_id,
            'leave_type_id' => $leaveRequest->leave_type_id,
            'date_from' => $leaveRequest->date_from->toDateString(),
            'date_to' => $leaveRequest->date_to->toDateString(),
        ];
    }
}
