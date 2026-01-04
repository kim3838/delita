<?php

namespace App\Transformers\Account;

use App\Facades\Fractal;
use App\Models\Account;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(Account $model): array
    {
        return [...Fractal::item($model, ListTransformer::class)];
    }
}
