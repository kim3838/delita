<?php

namespace App\Transformers\LeaveTypeAssignment;

use App\Models\Hydrations\Employee\LeaveTypeAssignment;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(LeaveTypeAssignment $leaveTypeAssignment): array
    {
        return [
            'value' => $leaveTypeAssignment->leave_type_id,
            'text' => "($leaveTypeAssignment->leave_type_code) " . $leaveTypeAssignment->leave_type_name,
        ];
    }
}
