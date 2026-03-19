<?php

namespace App\Transformers\EmployeePayrollComponent;

use App\Facades\Fractal;
use App\Models\Employee;
use App\Models\EmployeePayrollComponent;
use App\Transformers\PayFrequency\ItemTransformer as PayFrequencyItemTransformer;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(EmployeePayrollComponent $model): array
    {
        $employee = Employee::query()->find($model->employee_id);

        $payFrequency = $model->payFrequency
            ? Fractal::item($model->payFrequency, PayFrequencyItemTransformer::class)
            : null;

        $payrollGroup = $employee->payFrequency ? Fractal::item($employee->payFrequency, PayFrequencyItemTransformer::class) : null;

        $amount = is_numeric($model->amount)
            ? BigDecimal::of($model->amount)->toScale(2, RoundingMode::HalfUp)->toString()
            : $model->amount;

        return [
            'row_number' => $model->row_number,
            'id' => $model->id ? (int)$model->id : null,
            'employee_id' => $model->employee_id ? (int)$model->employee_id : null,
            'payroll_componentable_type' => $model->payroll_componentable_type,
            'payroll_componentable_id' => (int)$model->payroll_componentable_id,
            'formulable_type' => $model->formulable_type?->toArray(),
            'payroll_componentable' => [
                'name' => $model->payrollComponentable->name,
                'type' => $model->payrollComponentable->type?->toArray(),
            ],
            'amount' => $amount,
            'currency' => $model->currency,
            'pay_period' => $model->pay_period?->toArray(),
            'pay_type' => $model->pay_type?->toArray(),
            'pay_frequency_id' => $model->pay_frequency_id,
            'pay_frequency' => $payFrequency ? ['type' => $payFrequency['type']] : null,

            'amountable_start' => $model->amountable_start?->toArray(),
            'start_date' => $model->start_date?->format('Y-m-d'),
            'start_date_readable' => $model->start_date ? $model->start_date->format('M j,Y') : '--',

            'amountable_end' => $model->amountable_end?->toArray(),
            'end_date' => $model->end_date?->format('Y-m-d'),
            'end_date_readable' => $model->end_date ? $model->end_date->format('M j,Y') : '--',

            'employee' => [
                'ulid' => $model->employee_ulid,
                'number' => $model->employee_number,
                'full_name' => $model->employee_full_name,
                'payroll_group' => $payrollGroup,
            ],
        ];
    }
}
