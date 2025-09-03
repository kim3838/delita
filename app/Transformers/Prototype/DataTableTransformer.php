<?php

namespace App\Transformers\Prototype;

use App\Models\Hydrations\Prototype\DataTable as PrototypeDataTable;
use League\Fractal\TransformerAbstract;

class DataTableTransformer extends TransformerAbstract
{
    public function transform(PrototypeDataTable $model)
    {
        return [
            'row_number' => '#' . $model->row_number,
            'id' => (int)$model->id,
            'name' => $model->name,
            'code' => $model->code,
            'type' => $model->type,
            'category' => $model->category,
            'capacity' => $model->capacity,
            'json_data' => $model->json_data,
            'datetime_added' => $model->datetime_added?->format('Y-m-d H:i:s'),
            'created_at' => $model->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $model->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
