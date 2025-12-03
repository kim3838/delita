<?php

namespace App\Transformers\LeaveType;

use App\Models\LeaveType;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(LeaveType $model): array
    {
        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'row_number' => $model->row_number,
            'company_id' => $model->company_id,
            'code' => $model->code,
            'name' => $model->name,
            'type' => $model->type?->toArray(),
            'is_paid' => intval($model->is_paid),
            'monetizable' => intval($model->monetizable),

            'limit_usage' => intval($model->limit_usage),
            'limit_usage_span_type' => $model->limit_usage_span_type?->toArray(),
            'limit_usage_span_value' => $model->limit_usage_span_value,
            'limit_usage_value' => $model->limit_usage_value,

            'eligibility_employment_types' => $model->eligibility_employment_types,
            'initial_balance_upon_eligibility' => $model->initial_balance_upon_eligibility,

            'period_type' => $model->period_type?->toArray(),
            'period_interval_span_type' => $model->period_interval_span_type?->toArray(),
            'period_interval_span_value' => $model->period_interval_span_value,
            'period_calendar_span_value' => $model->period_calendar_span_value,

            'carry_over_balance_per_new_period' => intval($model->carry_over_balance_per_new_period),
            'carry_over_balance_type' => $model->carry_over_balance_type?->toArray(),
            'carry_over_balance_value' => $model->carry_over_balance_value,
        ];
    }
}
