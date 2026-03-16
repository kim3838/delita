<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class StandardAbsenceFormula
{
    public string $slug = 'standard-absence';

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
                'Totals' => $context->totals,
            ]);
        }

        $absencePenalty = BigDecimal::zero();

        $statementDetail = [
            'id' => null,
            'statement_level' => true,
            'formulable_type' => $formula->formulable_type->value,
            'component_type' => $formula->component_type->value,
            'component_name' => $formulableModel->name,
            'component_values' => null,
            'taxable' => 0.0,
            'nontaxable' => 0.0,
            'deduction' => (string)$absencePenalty->toScale(6, RoundingMode::HalfUp),
            'contribution' => 0.0,
            'withholding_tax' => 0.0,
            'net' => 0.0,
        ];

        $context->statementDetails[] = $statementDetail;

        return $next($context);
    }
}
