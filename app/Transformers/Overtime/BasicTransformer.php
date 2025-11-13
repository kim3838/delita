<?php

namespace App\Transformers\Overtime;

use App\Models\Overtime;
use League\Fractal\TransformerAbstract;

class BasicTransformer extends TransformerAbstract
{
    public function transform(Overtime $overtime): array
    {
        return [
            'id' => $overtime->id,
            'ulid' => $overtime->ulid,
            'start' => $overtime->start->format('Y-m-d H:i'),
            'end' => $overtime->end->format('Y-m-d H:i'),
        ];
    }
}
