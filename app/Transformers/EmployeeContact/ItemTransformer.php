<?php

namespace App\Transformers\EmployeeContact;

use App\Models\EmployeeContact;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(EmployeeContact $model)
    {
        return [
            'id' => $model->id,
            'employee_id' => $model->employee_id,
            'office_email' => $model->office_email,
            'personal_email' => $model->personal_email,
            'office_phone' => $model->office_phone,
            'personal_phone' => $model->personal_phone,
        ];
    }
}
