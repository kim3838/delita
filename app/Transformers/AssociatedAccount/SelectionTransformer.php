<?php

namespace App\Transformers\AssociatedAccount;

use App\Models\Hydrations\AssociatedAccount;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(AssociatedAccount $model): array
    {
        return [
            'value' => $model->account_id,
            'text' => $model->account_number,
        ];
    }
}
