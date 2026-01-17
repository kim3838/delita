<?php

namespace App\Transformers\EmployeePayrollComponent;

use App\Facades\Fractal;
use App\Models\Employee;
use App\Models\EmployeePayrollComponent;
use App\Transformers\PayFrequency\ItemTransformer as PayFrequencyItemTransformer;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(EmployeePayrollComponent $model): array
    {
        $employee = Employee::query()->find($model->employee_id);

        $payFrequency = $model->payFrequency
            ? Fractal::item($model->payFrequency, PayFrequencyItemTransformer::class)
            : null;

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
            'amount' => $model->amount,
            'currency' => $model->currency,
            'pay_period' => $model->pay_period?->toArray(),
            'pay_type' => $model->pay_type?->toArray(),
            'pay_frequency_id' => $model->pay_frequency_id,
            'pay_frequency' => $payFrequency ? ['type' => $payFrequency['type']] : null,

            'amountable_start' => $model->amountable_start?->toArray(),
            'start_date' => $model->start_date?->format('Y-m-d'),

            'amountable_end' => $model->amountable_end?->toArray(),
            'end_date' => $model->end_date?->format('Y-m-d'),

            'employee' => [
                'ulid' => $employee->ulid,
                'number' => $employee->number,
                'full_name' => $employee->full_name,
                'department' => $employee->departments->first(),
                'designation' => $employee->designation,
            ],
        ];
    }
}
