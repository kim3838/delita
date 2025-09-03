<?php

namespace App\Transformers\Account;

use App\Models\Account;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(Account $model): array
    {
        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'number' => $model->number,
            'plan' => $model->plan->toArray(),
            'date_registered' => $model->date_registered?->format('Y-m-d'),
        ];
    }
}
