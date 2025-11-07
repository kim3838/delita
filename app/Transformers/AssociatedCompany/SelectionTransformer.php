<?php

namespace App\Transformers\AssociatedCompany;

use App\Models\Hydrations\AssociatedCompany;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(AssociatedCompany $model): array
    {
        return [
            'value' => $model->company_id,
            'text' => $model->company_short_name,
            'payload' => [
                'currency' => $model->company_currency,
                'timezone' => $model->company_timezone,
                'assignment_type' => $model->assignment_type->toArray(),
            ]
        ];
    }
}
