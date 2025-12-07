<?php

namespace App\Transformers\LeaveType;

use App\Models\LeaveType;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(LeaveType $model): array
    {
        return [
            'value' => $model->id,
            'text' => "($model->code) " . $model->name,
        ];
    }
}
