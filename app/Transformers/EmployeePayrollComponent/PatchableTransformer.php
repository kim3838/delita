<?php

namespace App\Transformers\EmployeePayrollComponent;

use App\Models\EmployeePayrollComponent;
use League\Fractal\TransformerAbstract;

class PatchableTransformer extends TransformerAbstract
{
    public function transform(EmployeePayrollComponent $model): array
    {
        return [
            'employee_id' => $model?->employee_id !== null ? (int) $model->employee_id : null,
            'payroll_componentable_id' => $model?->payroll_componentable_id !== null ? (int) $model->payroll_componentable_id : null,
            'payroll_componentable_type' => $model->payroll_componentable_type,
            'amount' => $model->amount,
            'currency' => $model->currency,
            'pay_period' => $model->pay_period?->value,
            'pay_type' => $model->pay_type?->value,
            'pay_frequency_id' => $model->pay_frequency_id,
        ];
    }
}
