<?php

namespace App\Transformers\CompanyAssignment;

use App\Models\Hydrations\User\CompanyAssignment;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(CompanyAssignment $model): array
    {
        return [
            'id' => $model->company_user_id ?? '',
            'user_id' => $model->user_id,
            'company_id' => $model->company_id,
            'company_code' => $model->company_code,
            'company_name' => $model->company_name,
            'company_assignment_type' => $model->company_assignment_type?->value,
            'is_employee' => (bool)$model->employee_id,
            'employee_id' => $model->employee_id,
            'employee_number' => $model->employee_number,
            'employee_full_name' => $model->employee_full_name,
        ];
    }
}
