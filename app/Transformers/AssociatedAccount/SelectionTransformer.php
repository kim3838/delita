<?php

namespace App\Transformers\AssociatedAccount;

use App\Models\Account;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(Account $model): array
    {
        return [
            'value' => $model->id,
            'text' => $model->number,
        ];
    }
}
