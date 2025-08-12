<?php

namespace App\Transformers\Shift;

use App\Facades\Fractal;
use App\Models\Shift;
use App\Transformers\ShiftSchedule\ListTransformer as ShiftScheduleListTransformer;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(Shift $model): array
    {
        $schedules = $model->schedules->sortBy(function($item, $key){
            return $item->week_day->value;
        }, SORT_NUMERIC);

        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'company_id' => $model->company_id,
            'code' => $model->code,
            'name' => $model->name,
            'type' => $model->type->toArray(),
            'schedules' => Fractal::collection($schedules, ShiftScheduleListTransformer::class)['data']
        ];
    }
}
