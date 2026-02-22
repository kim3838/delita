<?php

namespace App\Transformers\Payroll;

use App\Models\Payroll;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(Payroll $payroll): array
    {
        return [
            'value' => $payroll->id,
            'text' => $payroll->number,
        ];
    }
}
