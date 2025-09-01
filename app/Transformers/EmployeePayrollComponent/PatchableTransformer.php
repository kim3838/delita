<?php

namespace App\Transformers\EmployeePayrollComponent;

use App\Models\EmployeePayrollComponent;
use Carbon\Carbon;
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

            'amountable_start' => $model->amountable_start?->value,
            'start_date' => $model->start_date ? Carbon::parse($model->start_date)->format('Y-m-d') : $model->start_date,

            'amountable_end' => $model->amountable_end?->value,
            'end_date' => $model->end_date ? Carbon::parse($model->end_date)->format('Y-m-d') : $model->end_date,
        ];
    }
}
