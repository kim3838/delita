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
            'name' => $model->company_name,
            'code' => $model->company_code,
            'timezone' => $model->company_timezone,
        ];
    }
}
