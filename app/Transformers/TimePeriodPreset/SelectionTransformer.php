<?php

namespace App\Transformers\TimePeriodPreset;

use App\Models\TimePeriodPreset;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(TimePeriodPreset $model): array
    {
        return [
            'value' => $model->id,
            'text' => $model->readable_name,
            'monthly_period' => $model->monthly_period,
            'semimonthly_period' => $model->semimonthly_period,
        ];
    }
}
