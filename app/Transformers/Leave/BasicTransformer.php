<?php

namespace App\Transformers\Leave;

use App\Models\Leave;
use League\Fractal\TransformerAbstract;

class BasicTransformer extends TransformerAbstract
{
    public function transform(Leave $leave): array
    {
        return [
            'id' => $leave->id,
            'ulid' => $leave->ulid,
            'date' => $leave->date->toDateString(),
            'leave_type' => $leave->leaveType?->toArray(),
        ];
    }
}
