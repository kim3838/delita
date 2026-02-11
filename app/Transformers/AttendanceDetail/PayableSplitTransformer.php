<?php

namespace App\Transformers\AttendanceDetail;

use App\Models\AttendanceDetail;
use Illuminate\Database\Eloquent\Relations\Relation;
use League\Fractal\TransformerAbstract;

class PayableSplitTransformer extends TransformerAbstract
{
    public function transform(AttendanceDetail $attendanceDetail): array
    {
        return [
            'id' => $attendanceDetail->id,
            'proxy_model' => Relation::getMorphAlias(AttendanceDetail::class),
            'split_duration' => $attendanceDetail->split_duration,
            'work_hour_type' => $attendanceDetail->work_hour_type,
            'regular_rate_multiplier' => (float)$attendanceDetail->regular_rate_multiplier,
            'non_rest_rate_multiplier' => (float)$attendanceDetail->non_rest_rate_multiplier,
            'hourly_rate_multiplier' => (float)$attendanceDetail->hourly_rate_multiplier,
            'base_rate_multiplier' => (float)$attendanceDetail->base_rate_multiplier,
            'actual_present' => (float)($attendanceDetail->actual_present ?? 0),
        ];
    }
}
