<?php

namespace App\Transformers\OvertimeRequest;

use App\Facades\Fractal;
use App\Models\OvertimeRequest;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{

    public function transform(OvertimeRequest $overtimeRequest): array
    {
        return [...Fractal::item($overtimeRequest, ListTransformer::class)];
    }
}
