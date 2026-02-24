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
            'taxable' => $model->taxable,
            'nontaxable' => $model->nontaxable,
            'deduction' => $model->deduction,
            'contribution' => $model->contribution,
            'withholding_tax' => $model->withholding_tax,
            'net' => $model->net,
        ];
    }
}
