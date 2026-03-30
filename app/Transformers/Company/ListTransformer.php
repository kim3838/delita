<?php

namespace App\Transformers\Company;

use App\Models\Company;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(Company $model): array
    {
        return [
            'id' => $model->company_id,
            'ulid' => $model->company_ulid,
            'account_number' => $model->account_number,
            'code' => $model->company_code,
            'short_name' => $model->company_short_name,
            'name' => $model->company_name,
            'address_line_1' => $model->company_address_line_1,
            'address_line_2' => $model->company_address_line_2,
            'city' => $model->company_city,
            'state' => $model->company_state,
            'postal_code' => $model->company_postal_code,
            'country' => $model->country_name,
            'currency' => $model->company_currency,
            'timezone' => $model->company_timezone,
        ];
    }
}
