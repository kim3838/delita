<?php

namespace App\Transformers\Company;

use App\Facades\Fractal;
use App\Models\Company;
use App\Transformers\Country\ItemTransformer as CountryItemTransformer;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(Company $model): array
    {
        $country = Fractal::item($model->country, CountryItemTransformer::class);

        return [
            'id' => $model->id,
            'ulid' => $model->ulid,
            'account_id' => $model->account_id,
            'country_id' => $model->country_id,
            'code' => $model->code,
            'short_name' => $model->short_name,
            'name' => $model->name,
            'address_line_1' => $model->address_line_1,
            'address_line_2' => $model->address_line_2,
            'city' => $model->city,
            'state' => $model->state,
            'postal_code' => $model->postal_code,
            'country' => $country,
            'currency' => $model->currency,
            'timezone' => $model->timezone,
        ];
    }
}
