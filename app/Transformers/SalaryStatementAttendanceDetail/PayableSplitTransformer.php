<?php

namespace App\Transformers\SalaryStatementAttendanceDetail;

use App\Models\SalaryStatementAttendanceDetail;
use Illuminate\Database\Eloquent\Relations\Relation;
use League\Fractal\TransformerAbstract;

class PayableSplitTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementAttendanceDetail $salaryStatementAttendanceDetail): array
    {
        return [
            'id' => $salaryStatementAttendanceDetail->id,
            'proxy_model' => Relation::getMorphAlias(SalaryStatementAttendanceDetail::class),
            'split_duration' => $salaryStatementAttendanceDetail->split_duration,
            'work_hour_type' => $salaryStatementAttendanceDetail->work_hour_type,
            'regular_rate_multiplier' => (float)$salaryStatementAttendanceDetail->regular_rate_multiplier,
            'non_rest_rate_multiplier' => (float)$salaryStatementAttendanceDetail->non_rest_rate_multiplier,
            'hourly_rate_multiplier' => (float)$salaryStatementAttendanceDetail->hourly_rate_multiplier,
            'base_rate_multiplier' => (float)$salaryStatementAttendanceDetail->base_rate_multiplier,
            'actual_present' => (float)($salaryStatementAttendanceDetail->actual_present ?? 0),
        ];
    }
}
