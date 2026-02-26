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
            'country_id' => $model->country_id,
            'code' => $model->code,
            'short_name' => $model->short_name,
            'address_line_1' => $model->address_line_1,
            'address_line_2' => $model->address_line_2,
            'name' => $model->name,
            'currency' => $model->currency,
            'timezone' => $model->timezone,
        ];
    }
}
