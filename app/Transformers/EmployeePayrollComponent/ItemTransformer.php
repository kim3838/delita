<?php

namespace App\Transformers\EmployeePayrollComponent;

use App\Models\EmployeePayrollComponent;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(EmployeePayrollComponent $model)
    {
        return [
            'id' => $model->id ? (int)$model->id : null,
            'employee_id' => $model->employee_id ? (int)$model->employee_id : null,
            'payroll_componentable_id' => (int)$model->payroll_componentable_id,
            'payroll_componentable_type' => $model->payroll_componentable_type,
            'payroll_componentable' => [
                'name' => $model->payrollComponentable->name,
                'type' => $model->payrollComponentable->type?->toArray(),
            ],
            'amount' => $model->amount,
            'currency' => $model->currency,
            'pay_period' => $model->pay_period?->toArray(),
            'pay_type' => $model->pay_type?->toArray(),
            'pay_frequency' => $model->pay_frequency?->toArray(),
        ];
    }
}
