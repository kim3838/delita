<?php

namespace App\Transformers\PayPeriodPreset;

use App\Models\TimePeriodPreset;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(TimePeriodPreset $model): array
    {
        return [
            'value' => $model->name,
            'text' => $model->readable_name,
            'monthly_period' => $model->monthly_period,
            'semimonthly_period' => $model->semimonthly_period,
        ];
    }
}
