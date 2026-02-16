<?php

namespace App\Transformers\AttendanceDetail;

use App\Models\AttendanceDetail;
use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Relations\Relation;
use League\Fractal\TransformerAbstract;

class PayableSplitTransformer extends TransformerAbstract
{
    public function transform(AttendanceDetail $attendanceDetail): array
    {
        return [
            'id' => $attendanceDetail->id,
            'proxy_model' => Relation::getMorphAlias(AttendanceDetail::class),
            'split_duration' => BigDecimal::of((string)$attendanceDetail->split_duration),
            'work_hour_type' => $attendanceDetail->work_hour_type,
            'regular_rate_multiplier' => BigDecimal::of($attendanceDetail->regular_rate_multiplier ?? 0),
            'non_rest_rate_multiplier' => BigDecimal::of($attendanceDetail->non_rest_rate_multiplier ?? 0),
            'hourly_rate_multiplier' => BigDecimal::of($attendanceDetail->hourly_rate_multiplier ?? 0),
            'base_rate_multiplier' => BigDecimal::of($attendanceDetail->base_rate_multiplier ?? 0),
            'actual_present' => BigDecimal::of((string)($attendanceDetail->actual_present ?? 0)),
        ];
    }
}
