<?php

namespace App\Transformers\AssociatedCompany;

use App\Models\Hydrations\AssociatedCompany;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(AssociatedCompany $model): array
    {
        return [
            'id' => $model->company_id,
            'ulid' => $model->company_ulid,
            'account_number' => $model->account_number,
            'code' => $model->company_code,
            'short_name' => $model->company_short_name,
            'name' => $model->company_name,
            'country' => $model->country_name,
            'currency' => $model->company_currency,
            'timezone' => $model->company_timezone,
        ];
    }
}
