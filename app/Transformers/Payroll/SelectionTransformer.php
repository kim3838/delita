<?php

namespace App\Transformers\Payroll;

use App\Models\Payroll;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(Payroll $payroll): array
    {
        $dateRangeReadable = $payroll->start_date->format('F j, Y') . ' - ' . $payroll->end_date->format('F j, Y');

        return [
            'value' => $payroll->id,
            'text' => $dateRangeReadable . PHP_EOL . $payroll->number,
        ];
    }
}
