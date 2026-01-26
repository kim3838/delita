<?php

namespace App\Transformers\LeaveRequest;

use App\Facades\Fractal;
use App\Models\LeaveRequest;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(LeaveRequest $leaveRequest): array
    {
        return [...Fractal::item($leaveRequest, ListTransformer::class)];
    }
}
