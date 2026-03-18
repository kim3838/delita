<?php

namespace App\Transformers\SalaryStatementDetail;

use App\Enums\Compensation;
use App\Enums\ComponentValueLabelMap;
use App\Enums\Deduction;
use App\Enums\Formulable;
use App\Enums\IncomeTax;
use App\Enums\PayslipColumn;
use App\Models\SalaryStatementDetail;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use League\Fractal\TransformerAbstract;

class PayslipTransformer extends TransformerAbstract
{
    public function transform(SalaryStatementDetail $salaryStatementDetail): array
    {
        $taxable = BigDecimal::of($salaryStatementDetail->taxable);
        $nontaxable = BigDecimal::of($salaryStatementDetail->nontaxable);
        $contribution = BigDecimal::of($salaryStatementDetail->contribution);
        $withholding_tax = BigDecimal::of($salaryStatementDetail->withholding_tax);
        $deduction = BigDecimal::of($salaryStatementDetail->deduction);
        $net = BigDecimal::of($salaryStatementDetail->net);

        $componentValues = $salaryStatementDetail->component_values;
        $componentValueType = $componentValues['type'] ?? null;

        $payslipViewable = false;
        $payslipColumn = null;
        $payslipItemName = null;
        $payslipItemValue = BigDecimal::zero();
        $payslipItemSubValues = [];
        $summary = [];

        switch($salaryStatementDetail->formulable_type)
        {
            case Formulable::EARNINGS:
                $payslipColumn = PayslipColumn::EARNINGS;
                switch ($salaryStatementDetail->component_type){
                    case Compensation::BASIC_PAY:
                    case Compensation::OVERTIME:
                        $payslipViewable = true;
                        $payslipItemName = $salaryStatementDetail->component_name ?? $salaryStatementDetail->component_sub_type?->label();
                        $payslipItemValue = $taxable;

                        foreach (array_keys($componentValues) as $key){

                            if(isValueInEnum(ComponentValueLabelMap::class, $key)){
                                $payslipItemSubValues[] = [
                                    'label' => ComponentValueLabelMap::from($key)->label(),
                                    'value' => BigDecimal::of($componentValues[$key])->toScale(2, RoundingMode::HalfUp)->toString()
                                ];
                            }
                        }
                        break;
                    case Compensation::STATUTORY_BENEFIT:
                    case Compensation::BENEFIT:
                    case Compensation::THIRTEENTH_MONTH_ADJUSTMENT:
                        $payslipViewable = true;
                        $payslipItemName = $salaryStatementDetail->component_name ?? $salaryStatementDetail->component_sub_type?->label();
                        $payslipItemValue = $taxable->plus($nontaxable);
                        break;
                    case Compensation::REGULAR_ALLOWANCE:
                    case Compensation::LEAVE_PAY:
                    case Compensation::HOLIDAY_PAY:
                    case Compensation::MANUAL_EARNING:
                        $payslipViewable = true;
                        $payslipItemName = $salaryStatementDetail->component_name ?? $salaryStatementDetail->component_sub_type?->label();
                        $payslipItemValue = $taxable;
                        break;
                    case Compensation::TAX_ADJUSTMENT:
                        $payslipViewable = true;
                        $payslipItemName = $salaryStatementDetail->component_name ?? $salaryStatementDetail->component_sub_type?->label();
                        $payslipItemValue = $nontaxable;
                        break;
                }
                break;
            case Formulable::DEDUCTIONS:
                $payslipColumn = PayslipColumn::DEDUCTIONS;

                switch ($salaryStatementDetail->component_type){
                    case Deduction::STATUTORY_CONTRIBUTION:
                        $payslipViewable = true;
                        $payslipItemName = $salaryStatementDetail->component_name ?? $salaryStatementDetail->component_sub_type?->label();
                        $payslipItemValue = $contribution;

                        $employeeShare = $componentValues['employee_share'] ?? null;

                        if(!empty($employeeShare)){

                            $employeeShare = collect($employeeShare)->sortByDesc(fn($value, $key) => $key)->toArray();

                            foreach (array_keys($employeeShare) as $key){

                                if(isValueInEnum(ComponentValueLabelMap::class, $key)){
                                    $payslipItemSubValues[] = [
                                        'label' => ComponentValueLabelMap::from($key)->label(),
                                        'value' => BigDecimal::of($employeeShare[$key])->toScale(2, RoundingMode::HalfUp)->toString()
                                    ];
                                }
                            }
                        }

                        break;
                    case Deduction::DEDUCTION:
                    case Deduction::MANUAL_DEDUCTION:
                    case Deduction::TAX_ADJUSTMENT:
                    case Deduction::THIRTEENTH_MONTH_ADJUSTMENT:
                        $payslipViewable = true;
                        $payslipItemName = $salaryStatementDetail->component_name ?? $salaryStatementDetail->component_sub_type?->label();
                        $payslipItemValue = $deduction;
                        break;
                }
                break;
            case Formulable::INCOME_TAX:
                $payslipColumn = PayslipColumn::DEDUCTIONS;

                switch ($salaryStatementDetail->component_type){
                    case IncomeTax::WITHHOLDING_TAX:
                        $payslipViewable = true;
                        $payslipItemName = $salaryStatementDetail->component_name ?? $salaryStatementDetail->component_sub_type?->label();
                        $payslipItemValue = $withholding_tax;
                        break;
                }
                break;
            case Formulable::NET_INCOME:
                $payslipColumn = PayslipColumn::SUMMARY;

                $payslipViewable = true;

                foreach (array_keys($componentValues) as $key){

                    if(in_array($key, ['gross', 'deduction', 'net'])){

                        $summary[$key] = BigDecimal::of($componentValues[$key])->toScale(2, RoundingMode::HalfUp)->toString();
                    }
                }
                break;
        }

        return [
            'row_number' => $salaryStatementDetail->row_number,
            'id' => $salaryStatementDetail->id,
            'formulable_type' => $salaryStatementDetail->formulable_type?->toArray(),
            'component_type' => $salaryStatementDetail->component_type?->toArray(),
            'component_sub_type' => $salaryStatementDetail->component_sub_type?->toArray(),
            'component_name' => $salaryStatementDetail->component_name,

            'component_value_type' => $componentValueType,
            'component_values' => empty($componentValues) ? [] : [$componentValues],

            'payslip_payload' => [
                'viewable' => $payslipViewable,
                'column' => $payslipColumn,
                'payroll_item_name' => $payslipItemName,
                'payroll_item_value' => $payslipItemValue->toScale(2, RoundingMode::HalfUp)->toString(),
                'payroll_item_sub_values' => $payslipItemSubValues,
                'summary' => $summary,
            ],

            'taxable' => $taxable->toScale(2, RoundingMode::HalfUp)->toString(),
            'nontaxable' => $nontaxable->toScale(2, RoundingMode::HalfUp)->toString(),
            'contribution' => $contribution->toScale(2, RoundingMode::HalfUp)->toString(),
            'withholding_tax' => $withholding_tax->toScale(2, RoundingMode::HalfUp)->toString(),
            'deduction' => $deduction->toScale(2, RoundingMode::HalfUp)->toString(),
            'net' => $net->toScale(2, RoundingMode::HalfUp)->toString(),
        ];
    }
}
