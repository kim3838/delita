<?php

namespace App\Transformers\SalaryStatementDetail;

use App\Models\SalaryStatementDetail;
use League\Fractal\TransformerAbstract;

class PipelineChainableTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementDetail $model): array
    {
        return [
            'id' => $model->id,
            'formulable_type' => $model->formulable_type?->value,
            'component_type' => $model->component_type?->value,
            'component_values' => $model->component_values,
            'taxable' => (float)$model->taxable,
            'nontaxable' => (float)$model->nontaxable,
            'deduction' => (float)$model->deduction,
            'contribution' => (float)$model->contribution,
            'withholding_tax' => (float)$model->withholding_tax,
            'net' => (float)$model->net,
        ];
    }
}
