<?php

namespace App\Transformers\LeaveRequestResult;

use App\Models\LeaveRequestResult;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(LeaveRequestResult $leaveRequestResult): array
    {
        return [
            'date' => $leaveRequestResult->date->toDateString(),
            'successful' => $leaveRequestResult->successful,
            'remarks' => $leaveRequestResult->remarks
        ];
    }
}
