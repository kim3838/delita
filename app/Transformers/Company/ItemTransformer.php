<?php

namespace App\Transformers\Company;

use App\Models\Company;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(Company $model): array
    {
        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'account_id' => $model->account_id,
            'code' => $model->code,
            'name' => $model->name,
            'timezone' => $model->timezone,
        ];
    }
}
