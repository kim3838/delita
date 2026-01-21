<?php

namespace App\Transformers\AttendanceAdjustmentRequest;

use App\Facades\Fractal;
use App\Models\AttendanceAdjustmentRequest;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(AttendanceAdjustmentRequest $attendanceAdjustmentRequest): array
    {
        return [...Fractal::item($attendanceAdjustmentRequest, ListTransformer::class)];
    }
}
