<?php

namespace App\Transformers\AssociatedAccount;

use App\Models\Hydrations\AssociatedAccount;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(AssociatedAccount $model): array
    {
        return [
            'id' => $model->account_id,
            'ulid' => $model->account_ulid,
            'number' => $model->account_number,
            'type' => $model->account_type->toArray(),
            'date_registered' => Carbon::parse($model->account_date_registered)->toDateString()
        ];
    }
}
