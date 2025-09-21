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

        $workStartGraceTime = $model->work_start_grace_time > 0
            ? "{$model->work_start_grace_time} Minutes"
            : "No grace time";

        $lunchStartGraceTime = $model->lunch_start_grace_time > 0
            ? "{$model->lunch_start_grace_time} Minutes"
            : "No grace time";

        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'row_number' => $model->row_number,
            'company_id' => $model->company_id,
            'code' => $model->code,
            'name' => $model->name,
            'type' => $model->type->toArray(),
            'work_start_grace_time' => $workStartGraceTime,
            'require_lunch_time_in_and_out' => $model->require_lunch_time_in_and_out ? 'Yes' : 'No',
            'lunch_start_grace_time' => $model->require_lunch_time_in_and_out ? $lunchStartGraceTime : '',
            'schedules' => Fractal::collection($schedules, ShiftScheduleListTransformer::class)['data']
        ];
    }
}
