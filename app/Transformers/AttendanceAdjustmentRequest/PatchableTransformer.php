<?php

namespace App\Transformers\AttendanceAdjustmentRequest;

use App\Models\AttendanceAdjustmentRequest;
use League\Fractal\TransformerAbstract;

class PatchableTransformer extends TransformerAbstract
{
    public function transform(AttendanceAdjustmentRequest $attendanceAdjustmentRequest): array
    {
        return [
            'company_id' => $attendanceAdjustmentRequest->company_id,
            'first_in' => $attendanceAdjustmentRequest->first_in->format('Y-m-d H:i'),
            'lunch_out' => $attendanceAdjustmentRequest->lunch_out?->format('Y-m-d H:i'),
            'lunch_in' => $attendanceAdjustmentRequest->lunch_in?->format('Y-m-d H:i'),
            'last_out' => $attendanceAdjustmentRequest->last_out->format('Y-m-d H:i'),
        ];
    }
}
