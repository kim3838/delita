<?php

namespace App\Transformers\Country;

use App\Models\Country;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(Country $model): array
    {
        return [
            'id' => $model->id,
            'iso2' => $model->iso2,
            'name' => $model->name,
            'phone_code' => $model->phone_code,
            'iso3' => $model->iso3,
            'region' => $model->region,
            'subregion' => $model->subregion,
        ];
    }
}
