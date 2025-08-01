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
            'name' => $model->company_name,
            'country' => $model->country_name,
            'currency' => $model->company_currency,
            'timezone' => $model->company_timezone,
        ];
    }
}
