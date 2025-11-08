<?php

namespace App\Transformers\Account;

use App\Models\Account;
use League\Fractal\TransformerAbstract;

class BasicTransformer extends TransformerAbstract
{
    public function transform(Account $model): array
    {
        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'subscriptions' => $model->subscriptions->map(function ($subscription) {
                return $subscription->module->toArray();
            })
        ];
    }
}
