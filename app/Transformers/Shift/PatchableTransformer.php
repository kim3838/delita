<?php

namespace App\Transformers\Shift;

use App\Models\Shift;
use Illuminate\Support\Arr;
use League\Fractal\TransformerAbstract;

class PatchableTransformer extends TransformerAbstract
{
    public function transform(Shift $model): array
    {
        return [
            'company_id' => $model->company_id,
            'code' => $model->code,
            'name' => $model->name,
            'type' => $model->type->value,
            'holiday_policy' => $model->holiday_policy->value,
            'except_holidays' => is_array($model->except_holidays)
                ? array_values(Arr::sort($model->except_holidays))
                : [],
            'work_start_grace_time' => $model->work_start_grace_time,
            'require_lunch_time_in_and_out' => intval($model->require_lunch_time_in_and_out),
            'lunch_start_grace_time' => $model->lunch_start_grace_time,
            'max_overtime' => $model->max_overtime,
        ];
    }
}
