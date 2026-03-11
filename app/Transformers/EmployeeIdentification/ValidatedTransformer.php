<?php

namespace App\Transformers\EmployeeIdentification;

use App\Models\EmployeeIdentification;
use League\Fractal\TransformerAbstract;

class ValidatedTransformer extends TransformerAbstract
{
    public function transform(EmployeeIdentification $model): array
    {
        return [
            'id' => $model->id,
            'employee_id' => $model->employee_id,
            'type' => $model->type?->toArray(),
            'number' => $model->number,
            'readable_number' => $model->readable_number,
        ];
    }
}
