<?php

namespace App\Transformers\PayFrequency;

use App\Models\PayFrequency;
use League\Fractal\TransformerAbstract;

class PatchableTransformer extends TransformerAbstract
{
    public function transform(PayFrequency $model): array
    {
        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'company_id' => $model->company_id,
            'code' => $model->code,
            'order' => $model->order,
            'type' => $model->type->value,
            'time_period_preset_id' => $model->time_period_preset_id,
            'period' => $model->period?->cast,
            'cutoff_type' => $model->cutoff_type ? $model->cutoff_type->value : null,
            'cut_off_value' => $model->cut_off_value ? $model->cut_off_value->value : null,
            'days_span' => $model->days_span,
        ];
    }
}
