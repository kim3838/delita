<?php

namespace App\Transformers\Employee;

use App\Facades\Fractal;
use App\Models\Employee;
use App\Transformers\PayFrequency\ItemTransformer as PayFrequencyItemTransformer;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(Employee $employee): array
    {
        $payrollGroup = $employee->payFrequency ? Fractal::item($employee->payFrequency, PayFrequencyItemTransformer::class) : null;

        return [
            'value' => $employee->id,
            'text' => "($employee->number) " . $employee->full_name,
            'payroll_group' => $payrollGroup ? [
                'value' => $payrollGroup['id'] ?? null,
                'type_value' => $payrollGroup['type']['value'] ?? null
            ]: null
        ];
    }
}
