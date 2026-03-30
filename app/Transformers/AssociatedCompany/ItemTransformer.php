<?php

namespace App\Transformers\AssociatedCompany;

use App\Models\Hydrations\AssociatedCompany;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(AssociatedCompany $model): array
    {
        return [
            'id' => $model->company_id,
            'ulid' => $model->company_ulid,
            'account_id' => $model->account_id,
            'country_id' => $model->country_id,
            'code' => $model->company_code,
            'short_name' => $model->company_short_name,
            'name' => $model->company_name,
            'address_line_1' => $model->company_address_line_1,
            'address_line_2' => $model->company_address_line_2,
            'city' => $model->company_city,
            'state' => $model->company_state,
            'postal_code' => $model->company_postal_code,
            'currency' => $model->company_currency,
            'timezone' => $model->company_timezone,
        ];
    }
}
