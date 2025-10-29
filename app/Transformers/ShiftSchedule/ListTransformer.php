<?php

namespace App\Transformers\ShiftSchedule;

use App\Models\ShiftSchedule;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(ShiftSchedule $model): array
    {
        return [
            'shift_id' => $model->shift_id,
            'week_day' => $model->week_day->toArray(),
            'week_day_name' => $model->week_day->label(),
            'is_rest_day' => $model->is_rest_day,
            'is_day_off' => $model->is_day_off,
            'is_flexible' => $model->is_flexible,
            'timezone' => $model->timezone,
            'work_start' => $model->work_start ? Carbon::parse($model->work_start)->format('H:i') : $model->work_start,
            'work_end' => $model->work_end ? Carbon::parse($model->work_end)->format('H:i') : $model->work_end,
            'total_work_hours_with_breaks' => $model->total_work_hours_with_breaks,
            'has_lunch_break' => (int)$model->has_lunch_break,
            'lunch_break_start' => $model->lunch_break_start ? Carbon::parse($model->lunch_break_start)->format('H:i') : $model->lunch_break_start,
            'lunch_break_end' => $model->lunch_break_end ? Carbon::parse($model->lunch_break_end)->format('H:i') : $model->lunch_break_end,
            'total_lunch_break_hours' => $model->total_lunch_break_hours,
        ];
    }
}
