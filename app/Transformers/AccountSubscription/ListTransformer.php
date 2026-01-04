<?php

namespace App\Transformers\AccountSubscription;

use App\Models\AccountSubscription;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(AccountSubscription $model): array
    {
        return [
            'id' => $model->id,
            'module' => $model->module?->toArray(),
            'plan' => $model->plan?->toArray(),
            'date_subscribed' => $model->date_subscribed?->format('Y-m-d')
        ];
    }
}
