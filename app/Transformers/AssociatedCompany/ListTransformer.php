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
            'name' => $model->company_name,
            'code' => $model->company_code,
            'timezone' => $model->company_timezone,
        ];
    }
}
