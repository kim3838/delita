<?php

namespace App\Transformers\Prototype;

use App\Models\Hydrations\Prototype\DataTable as PrototypeDataTable;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class DataTableTransformer extends TransformerAbstract
{
    public function transform(PrototypeDataTable $model)
    {
        return [
            'row_number' => '#' . $model->row_number,
            'id' => (int)$model->id,
            'name' => html_entity_decode($model->name),
            'code' => $model->code,
            'type' => $model->type,
            'category' => $model->category,
            'capacity' => $model->capacity,
            'datetime_added' => Carbon::parse($model->datetime_added)->toDateTimeString(),
            'created_at' => Carbon::parse($model->created_at)->toDateTimeString(),
            'updated_at' => Carbon::parse($model->updated_at)->toDateTimeString()
        ];
    }
}
