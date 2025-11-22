<?php

namespace App\Transformers\EmployeePayrollComponent;

use App\Enums\AmountablePayrollComponentEnd;
use App\Enums\AmountablePayrollComponentStart;
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
            'formulable_type' => $model->formulable_type?->value,
            'amount' => $model->amount,
            'currency' => $model->currency,
            'pay_period' => $model->pay_period?->value,
            'pay_type' => $model->pay_type?->value,
            'pay_frequency_id' => $model->pay_frequency_id,

            'amountable_start' => $model->amountable_start
                ? $model->amountable_start->value
                : AmountablePayrollComponentStart::NOT_SPECIFIED,
            'start_date' => $model->start_date?->format('Y-m-d'),

            'amountable_end' => $model->amountable_end
                ? $model->amountable_end->value :
                AmountablePayrollComponentEnd::NOT_SPECIFIED,
            'end_date' => $model->end_date?->format('Y-m-d'),
        ];
    }
}
