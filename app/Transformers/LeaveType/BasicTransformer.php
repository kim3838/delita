<?php

namespace App\Transformers\LeaveType;

use App\Models\LeaveType;
use League\Fractal\TransformerAbstract;

class BasicTransformer extends TransformerAbstract
{
    public function transform(LeaveType $leave): array
    {
        return [
            'id' => $leave->id,
            'ulid' => $leave->ulid,
            'code' => $leave->code,
        ];
    }
}
