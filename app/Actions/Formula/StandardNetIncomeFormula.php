<?php

namespace App\Actions\Formula;

use App\Concrete\SalaryStatementContext;
use App\Enums\SalaryStatementDetailComponentValueType;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class StandardNetIncomeFormula
{
    public string $slug = 'standard-net-income';

    public function handle(SalaryStatementContext $context, $next)
    {
        $debugEnabled = false;

        $pipelinePayload = $context->pipelinePayload->where('formula_slug', $this->slug)->first();
        $formula = $pipelinePayload['formula'];

        $totalTaxable = BigDecimal::of($context->totals['taxable'] ?? '0');
        $totalTaxableBonus = BigDecimal::of($context->totals['taxable_bonus'] ?? '0');

        $totalNonTaxable = BigDecimal::of($context->totals['nontaxable'] ?? '0');

        $totalContribution = BigDecimal::zero();
        $totalDeduction = BigDecimal::zero();
        $totalWithholdingTax = BigDecimal::of($context->totals['withholding_tax'] ?? '0');

        $gross = BigDecimal::zero();

        foreach ($context->statementDetails as $detail) {
            $totalContribution = $totalContribution->plus(BigDecimal::of((string)$detail['contribution']));
            $totalDeduction = $totalDeduction->plus(BigDecimal::of((string)$detail['deduction']));
        }

        $totalTaxableAfterContribution = $totalTaxable
            ->plus($totalTaxableBonus)
            ->minus($totalContribution);

        $gross = $gross
            ->plus($totalTaxable)
            ->plus($totalTaxableBonus)
            ->plus($totalNonTaxable);

        $deduction = $totalWithholdingTax
            ->plus($totalContribution)
            ->plus($totalDeduction);

        $totalNet = $gross->minus($deduction);

        $context->totals = [
            ...$context->totals,
            'taxable' => $totalTaxableAfterContribution->toScale(6, RoundingMode::HalfUp)->toString(),
            'deduction' => $totalDeduction->toScale(6, RoundingMode::HalfUp)->toString(),
            'net' => $totalNet->toScale(6, RoundingMode::HalfUp)->toString()
        ];

        if($debugEnabled){
            _debug([
                'Formula slug' => $this->slug,
                'Totals' => $context->totals,
                'Total taxable' => $totalTaxable->toScale(6, RoundingMode::HalfUp)->toString(),
            ]);
        }

        $componentValues = [
            'type' => SalaryStatementDetailComponentValueType::NET->value,
            'gross' => $gross->toScale(2, RoundingMode::HalfUp)->toString(),
            'deduction' => $deduction->toScale(2, RoundingMode::HalfUp)->toString(),
            'net' => $totalNet->toScale(2, RoundingMode::HalfUp)->toString(),
        ];

        $statementDetail = [
            'id' => null,
            'formulable_type' => $formula->formulable_type->value,
            'component_type' => null,
            'component_name' => null,
            'component_values' => $componentValues,
            'taxable' => 0.0,
            'nontaxable' => 0.0,
            'deduction' => 0.0,
            'contribution' => 0.0,
            'withholding_tax' => 0.0,
            'net' => (string)$totalNet->toScale(6, RoundingMode::HalfUp)
        ];

        $context->statementDetails[] = $statementDetail;

        return $next($context);
    }
}
