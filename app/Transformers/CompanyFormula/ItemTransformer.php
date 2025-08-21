<?php

namespace App\Transformers\CompanyFormula;

use App\Models\CompanyFormula;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(CompanyFormula $model): array
    {
        return [
            'id' => $model->id,
            'formula_id' => $model->formula_id,
            'company_id' => $model->company_id,
            'settings' => $model->settings?->cast,
        ];
    }
}
