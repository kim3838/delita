<?php

namespace App\Transformers\AssociatedAccount;

use App\Facades\TimeZoneConverterFacade;
use App\Models\Account;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(Account $model): array
    {
        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'number' => $model->number,
            'plan' => $model->plan->toArray(),
            'date_registered' => TimeZoneConverterFacade::globalToLocal($model->date_registered),
            'subscriptions' => $model->subscriptions->map(function ($subscription) {
                return [
                    'id' => $subscription->id,
                    'module' => $subscription->module->toArray(),
                    'date_subscribed' => TimeZoneConverterFacade::globalToLocal($subscription->date_subscribed)
                ];
            })
        ];
    }
}
