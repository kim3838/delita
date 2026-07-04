<?php

namespace App\Transformers\Shift;

use App\Helpers\TimeHelper;
use App\Models\Shift;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(Shift $model): array
    {
        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'company_id' => $model->company_id,
            'code' => $model->code,
            'name' => $model->name,
            'type' => $model->type?->toArray(),
            'holiday_policy' => $model->holiday_policy?->toArray(),
            'except_holidays' => is_array($model->except_holidays) ? $model->except_holidays : [],
            'work_start_grace_time' => $model->work_start_grace_time,
            'work_start_grace_time_readable' => $model->work_start_grace_time > 0
                ? $model->work_start_grace_time . ($model->work_start_grace_time > 1 ? ' Minutes' : ' Minute')
                : 'No Grace',
            'require_lunch_time_in_and_out' => intval($model->require_lunch_time_in_and_out),
            'lunch_start_grace_time' => $model->lunch_start_grace_time,
            'lunch_start_grace_time_readable' => $model->lunch_start_grace_time > 0
                ? $model->lunch_start_grace_time . ($model->lunch_start_grace_time > 1 ? ' Minutes' : ' Minute')
                : 'No Grace',
            'automatic_overtime' => intval($model->automatic_overtime),
            'automatic_overtime_readable' => $model->automatic_overtime ? 'Yes' : 'No',
            'max_overtime' => $model->max_overtime,
            'max_overtime_readable' => $model->max_overtime > 0 ? TimeHelper::minutesToTime($model->max_overtime * 60): 'No overtime',
        ];
    }
}
