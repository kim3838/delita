<?php

namespace App\Transformers\PayFrequency;

use App\Models\PayFrequency;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(PayFrequency $model): array
    {
        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'company_id' => $model->company_id,
            'code' => $model->code,
            'order' => $model->order,
            'type' => $model->type->toArray(),
            'period_preset_readable_name' => $model->timePeriodPreset ? $model->timePeriodPreset?->readable_name : null,
            'period' => $model->period ? $model->period->cast : null,
            'cutoff_type' => $model->cutoff_type ? $model->cutoff_type->toArray() : null,
            'cut_off_value' => $model->cut_off_value ? $model->cut_off_value->toArray() : null,
            'days_span' => $model->days_span,
        ];
    }
}
