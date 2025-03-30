<?php

namespace App\Transformers\Prototype;

use App\Models\Prototype;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(Prototype $model): array
    {
        return [
            'value' => (int)$model->id,
            'text' => $model->code . PHP_EOL . $model->name
        ];
    }
}
