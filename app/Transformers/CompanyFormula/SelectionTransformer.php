<?php

namespace App\Transformers\CompanyFormula;

use App\Models\Hydrations\CompanyFormula\Selection as FormulaSelection;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(FormulaSelection $model): array
    {
        return [
            'value' => $model->id,
            'text' => $model->name,
        ];
    }
}
