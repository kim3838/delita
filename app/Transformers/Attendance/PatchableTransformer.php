<?php

namespace App\Transformers\Attendance;

use App\Models\Attendance;
use League\Fractal\TransformerAbstract;

class PatchableTransformer extends TransformerAbstract
{
    public function transform(Attendance $attendance): array
    {
        return [
            'id' => $attendance->id,
            'date' => $attendance->date->toDateString(),
            'first_in' => $attendance->first_in->format('Y-m-d H:i'),
            'lunch_out' => $attendance->lunch_out?->format('Y-m-d H:i'),
            'lunch_in' => $attendance->lunch_in?->format('Y-m-d H:i'),
            'last_out' => $attendance->last_out->format('Y-m-d H:i'),
            'status' => $attendance->status?->value,
        ];
    }
}
