<?php

namespace App\Transformers\PayrollRequest;

use App\Facades\Fractal;
use App\Models\PayrollRequest;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(PayrollRequest $payrollRequest): array
    {
        return [...Fractal::item($payrollRequest, ListTransformer::class)];
    }
}
