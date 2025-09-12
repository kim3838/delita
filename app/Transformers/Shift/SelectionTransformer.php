<?php

namespace App\Transformers\Shift;

use App\Models\Shift;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(Shift $model): array
    {
        return [
            'value' => $model->id,
            'text' => $model->code . PHP_EOL . $model->name,
        ];
    }
}
