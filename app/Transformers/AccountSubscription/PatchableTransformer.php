<?php

namespace App\Transformers\AccountSubscription;

use App\Models\AccountSubscription;
use League\Fractal\TransformerAbstract;

class PatchableTransformer extends TransformerAbstract
{
    public function transform(AccountSubscription $model): array
    {
        return [
            'id' => $model->id,
            'account_id' => $model->account_id,
            'module' => $model->module?->value,
            'plan' => $model->plan?->value,
            'date_subscribed' => $model->date_subscribed?->format('Y-m-d')
        ];
    }
}
