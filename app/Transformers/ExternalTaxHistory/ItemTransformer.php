<?php

namespace App\Transformers\ExternalTaxHistory;

use App\Facades\Fractal;
use App\Models\ExternalTaxHistory;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(ExternalTaxHistory $externalTaxHistory): array
    {
        return [
            ...Fractal::item($externalTaxHistory, ListTransformer::class)
        ];
    }
}
