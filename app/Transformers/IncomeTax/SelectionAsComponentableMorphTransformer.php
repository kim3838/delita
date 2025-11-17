<?php

namespace App\Transformers\IncomeTax;

use App\Models\IncomeTax;
use League\Fractal\TransformerAbstract;

class SelectionAsComponentableMorphTransformer extends TransformerAbstract
{
    public function transform(IncomeTax $model): array
    {
        return [
            'value' => $model->id . '.income_tax',
            'text' => $model->name,
            'type' => $model->type?->toArray(),
            'assignable' => $model->assignable,
        ];
    }
}
