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
            'text' => $model->company_name,
            'payload' => [
                'assignment_type' => $model->assignment_type->toArray()
            ]
        ];
    }
}
