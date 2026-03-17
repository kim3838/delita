<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;
use App\Enums\FormulableComponentSubType;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class ManualDeductionFormula
{
    public string $slug = 'manual-deduction';

    public function handle(SalaryStatementContext $context, $next)
    {
        $debugEnabled = false;

        $pipelinePayload = $context->pipelinePayload->where('formula_slug', $this->slug)->first();
        $formula = $pipelinePayload['formula'];

        $manualDeductableComponentSubTypes = [FormulableComponentSubType::MANUAL_DEDUCTION->value];

        foreach($context->manualSalaryStatementItems as $componentSubType => $items)
        {
            if(in_array($componentSubType, $manualDeductableComponentSubTypes)){

                foreach($items as $item){

                    $name = $item['component_name'];
                    $deduction = BigDecimal::of($item['deduction']);

                    if($debugEnabled){
                        _debug([
                            'Formula slug' => $this->slug,
                            'Formula' => get_class($formula),
                            'Formulable type' => $formula->formulable_type->value,
                            'Formulable component type' => $formula->component_type->value,
                            'Name' => $name,
                            'Deduction' => $deduction->toString(),
                        ]);
                    }

                    if($deduction->toScale(2, RoundingMode::HalfUp)->isGreaterThan(BigDecimal::zero())){

                        $statementDetail = [
                            'id' => null,
                            'statement_level' => true,
                            'formulable_type' => $formula->formulable_type->value,
                            'component_type' => $formula->component_type->value,
                            'component_sub_type' => $componentSubType,
                            'component_name' => $name,
                            'component_values' => null,
                            'taxable' => 0.0,
                            'nontaxable' => 0.0,
                            'deduction' => $deduction->toScale(4, RoundingMode::HalfUp)->toString(),
                            'contribution' => 0.0,
                            'withholding_tax' => 0.0,
                            'net' => 0.0,
                        ];

                        $context->statementDetails[] = $statementDetail;
                    }
                }
            }
        }

        return $next($context);
    }
}
