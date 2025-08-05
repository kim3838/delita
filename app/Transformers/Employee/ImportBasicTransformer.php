<?php

namespace App\Transformers\Employee;

use App\Models\Employee;
use League\Fractal\TransformerAbstract;

class ImportBasicTransformer extends TransformerAbstract
{
    public function transform(Employee $model): array
    {
        return [
            'number' => $model->number,
            'family_name' => $model->family_name,
            'given_name' => $model->given_name,
        ];
    }
}
