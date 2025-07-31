<?php

namespace App\Transformers\NonEmployeeUser;

use App\Models\Hydrations\NonEmployeeUser;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(NonEmployeeUser $model): array
    {
        return [
            'value' => $model->id,
            'text' => $model->name . PHP_EOL . $model->email,
            'payload' => [
                'email' => $model->email
            ]
        ];
    }
}
