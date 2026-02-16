<?php

namespace App\Transformers\SalaryStatementAttendanceDetail;

use App\Models\SalaryStatementAttendanceDetail;
use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Relations\Relation;
use League\Fractal\TransformerAbstract;

class PayableSplitTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementAttendanceDetail $salaryStatementAttendanceDetail): array
    {
        return [
            'id' => $salaryStatementAttendanceDetail->id,
            'proxy_model' => Relation::getMorphAlias(SalaryStatementAttendanceDetail::class),
            'split_duration' => BigDecimal::of((string)$salaryStatementAttendanceDetail->split_duration),
            'work_hour_type' => $salaryStatementAttendanceDetail->work_hour_type,
            'regular_rate_multiplier' => BigDecimal::of($salaryStatementAttendanceDetail->regular_rate_multiplier ?? 0),
            'non_rest_rate_multiplier' => BigDecimal::of($salaryStatementAttendanceDetail->non_rest_rate_multiplier ?? 0),
            'hourly_rate_multiplier' => BigDecimal::of($salaryStatementAttendanceDetail->hourly_rate_multiplier ?? 0),
            'base_rate_multiplier' => BigDecimal::of($salaryStatementAttendanceDetail->base_rate_multiplier ?? 0),
            'actual_present' => BigDecimal::of((string)($salaryStatementAttendanceDetail->actual_present ?? 0)),
        ];
    }
}
