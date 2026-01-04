<?php

namespace App\Transformers\Account;

use App\Facades\Fractal;
use App\Models\Account;
use App\Transformers\AccountSubscription\ListTransformer as AccountSubscriptionListTransformer;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(Account $model): array
    {
        $subscriptions = $model->subscriptions->isEmpty()
            ? []
            : Fractal::collection($model->subscriptions->sortBy(function($item, $key){
                return $item->module->value;
            }, SORT_NUMERIC), AccountSubscriptionListTransformer::class)['data'];

        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'number' => $model->number,
            'email' => $model->email,
            'date_registered' => $model->date_registered?->format('Y-m-d'),
            'subscriptions' => $subscriptions
        ];
    }
}
