<?php

namespace App\Transformers\Account;

use App\Models\Account;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(Account $model): array
    {
        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'number' => $model->number,
            'type' => $model->type->toArray(),
            'date_registered' => Carbon::parse($model->date_registered)->toDateString()
        ];
    }
}
