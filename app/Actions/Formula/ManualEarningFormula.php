<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;
use App\Enums\FormulableComponentSubType;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class ManualEarningFormula
{
    public string $slug = 'manual-earning';

    public function handle(SalaryStatementContext $context, $next)
    {
        $debugEnabled = false;

        $pipelinePayload = $context->pipelinePayload->where('formula_slug', $this->slug)->first();
        $formula = $pipelinePayload['formula'];

        $totalTaxable = BigDecimal::of($context->totals['taxable'] ?? '0');

        $manualEarningableComponentSubTypes = [FormulableComponentSubType::MANUAL_EARNING->value];

        foreach($context->manualSalaryStatementItems as $componentSubType => $items)
        {
            if(in_array($componentSubType, $manualEarningableComponentSubTypes)){

                foreach($items as $item){

                    $name = $item['component_name'];
                    $taxableEarning = BigDecimal::of($item['taxable']);

                    if($debugEnabled){
                        _debug([
                            'Formula slug' => $this->slug,
                            'Formula' => get_class($formula),
                            'Formulable type' => $formula->formulable_type->value,
                            'Formulable component type' => $formula->component_type->value,
                            'Name' => $name,
                            'Taxable earning' => $taxableEarning->toString(),
                        ]);
                    }

                    if($taxableEarning->toScale(2, RoundingMode::HalfUp)->isGreaterThan(BigDecimal::zero())){

                        $totalTaxable = $totalTaxable->plus($taxableEarning);

                        $statementDetail = [
                            'id' => null,
                            'statement_level' => true,
                            'formulable_type' => $formula->formulable_type->value,
                            'component_type' => $formula->component_type->value,
                            'component_sub_type' => $componentSubType,
                            'component_name' => $name,
                            'component_values' => null,
                            'taxable' => $taxableEarning->toScale(4, RoundingMode::HalfUp)->toString(),
                            'nontaxable' => 0.0,
                            'deduction' => 0.0,
                            'contribution' => 0.0,
                            'withholding_tax' => 0.0,
                            'net' => 0.0,
                        ];

                        $context->statementDetails[] = $statementDetail;
                    }
                }
            }
        }

        $context->totals['taxable'] = $totalTaxable->toScale(4, RoundingMode::HalfUp)->toString();

        return $next($context);
    }
}
