<?php

namespace App\Transformers\OvertimeRequest;

use App\Models\OvertimeRequest;
use League\Fractal\TransformerAbstract;

class PatchableTransformer extends TransformerAbstract
{
    public function transform(OvertimeRequest $overtimeRequest): array
    {
        return [
            'company_id' => $overtimeRequest->company_id,
            'attendance_id' => $overtimeRequest->attendance_id,
            'start' => $overtimeRequest->start->format('Y-m-d H:i'),
            'end' => $overtimeRequest->end->format('Y-m-d H:i'),
            'duration' => $overtimeRequest->duration,
        ];
    }
}
