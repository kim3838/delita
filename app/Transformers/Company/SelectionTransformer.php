<?php

namespace App\Transformers\Company;

use App\Models\Company;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(Company $model): array
    {
        return [
            'value' => $model->id,
            'text' => $model->name,
            'payload' => [
                'currency' => $model->currency,
                'timezone' => $model->timezone,
            ]
        ];
    }
}
