<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;

class StandardCompensationTaxFormula
{
    public string $slug = 'standard-compensation-tax';

    public function handle(SalaryStatementContext $context, $next)
    {
        $debugEnabled = false;
        $pipelinePayload = $context->pipelinePayload->where('formula_slug', $this->slug)->first();
        $formulableModel = $pipelinePayload['formulable_model'];
        $companyFormula = $formulableModel->companyFormula;
        $formulaSettings = $companyFormula->settings;
        $formula = $pipelinePayload['formula'];

        if($debugEnabled){
            _debug([
                'Formula slug' => $this->slug,
                'Formulable' => get_class($formulableModel),
                'Company formula' => get_class($companyFormula),
                'Formula' => get_class($formula),
                'Shared' => $context->shared,
                'Formula settings' => $formulaSettings->cast,
                'Statement details' => $context->statementDetails
            ]);
        }

        return $next($context);
    }
}
