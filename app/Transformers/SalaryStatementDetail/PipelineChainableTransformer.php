<?php

namespace App\Transformers\SalaryStatementDetail;

use App\Models\SalaryStatementDetail;
use League\Fractal\TransformerAbstract;

class PipelineChainableTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementDetail $model): array
    {
        return [
            'formulable_type' => $model->formulable_type?->value,
            'component_type' => $model->component_type?->value,
            'taxable' => (float)$model->taxable,
            'nontaxable' => (float)$model->nontaxable,
            'deduction' => (float)$model->deduction,
            'contribution' => (float)$model->contribution,
            'withholding_tax' => (float)$model->withholding_tax,
            'net' => (float)$model->net,
            'employer_contribution' => (float)$model->employer_contribution,
        ];
    }
}
