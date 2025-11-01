<?php

namespace App\Transformers\Attendance;

use App\Facades\Fractal;
use App\Models\Attendance;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(Attendance $attendance): array
    {
        return [
            ...Fractal::item($attendance, ListTransformer::class)
        ];
    }
}
