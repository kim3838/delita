<?php

namespace App\Transformers\Prototype;

use App\Facades\TimeZoneConverterFacade;
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
            'datetime_added' => TimeZoneConverterFacade::globalToLocal($model->datetime_added, 'Y-m-d H:i:s'),
            'created_at' => TimeZoneConverterFacade::globalToLocal($model->created_at, 'Y-m-d H:i:s'),
            'updated_at' => TimeZoneConverterFacade::globalToLocal($model->updated_at, 'Y-m-d H:i:s'),
        ];
    }
}
