<?php

namespace App\Transformers\EmployeePayrollComponent;

use App\Facades\Fractal;
use App\Facades\TimeZoneConverterFacade;
use App\Models\EmployeePayrollComponent;
use App\Transformers\PayFrequency\ItemTransformer as PayFrequencyItemTransformer;
use League\Fractal\TransformerAbstract;

class ValidatedTransformer extends TransformerAbstract
{
    public function transform(EmployeePayrollComponent $model)
    {
        $payFrequency = $model->payFrequency ? Fractal::item($model->payFrequency, PayFrequencyItemTransformer::class) : null;

        return [
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
            'pay_frequency' => $payFrequency,

            'amountable_start' => $model->amountable_start?->toArray(),
            'start_date' => $model->start_date?->toDateString(),

            'amountable_end' => $model->amountable_end?->toArray(),
            'end_date' => $model->end_date?->toDateString(),
        ];
    }
}
