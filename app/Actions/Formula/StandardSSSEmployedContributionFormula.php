<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;

class StandardSSSEmployedContributionFormula
{
    public string $slug = 'standard-sss-employed-contribution';

    public function handle(SalaryStatementContext $context, $next)
    {
        _debug([
            'Pipeline' => 'StandardSSSEmployedContributionFormula',
        ]);

        $pipelinePayload = $context->pipelinePayload->where('formula_slug', $this->slug)->first();
        $formulableModel = $pipelinePayload['formulable_model'];
        $companyFormula = $formulableModel->companyFormula;
        $formulaSettings = $companyFormula->settings;
        $formula = $pipelinePayload['formula'];

        _debug([
            'Formulable' => get_class($formulableModel),
            'Company formula' => get_class($companyFormula),
            'Formula' => get_class($formula),
            'Formula settings' => $formulaSettings,
            'Statement details' => $context->statementDetails
        ]);

        $result = [
            'formulable_type' => $formula->formulable_type->value,
            'component_type' => $formula->component_type->value,
            'component_name' => $formulableModel->name,
            'taxable' => 0.0,
            'nontaxable' => 0.0,
            'deduction' => 1200.0,
            'contribution' => 0.0,
            'withholding_tax' => 0.0,
            'net' => 0.0,
            'employer_contribution' => 0.0,
        ];

        $context->statementDetails[] = $result;

        return $next($context);
    }
}
