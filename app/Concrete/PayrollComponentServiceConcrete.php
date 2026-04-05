<?php

namespace App\Concrete;

use App\Blueprint\PayrollComponentServiceInterface;
use App\Blueprint\Repositories\CompensationRepository;
use App\Blueprint\Repositories\DeductionRepository;
use App\Blueprint\Repositories\IncomeTaxRepository;
use App\Enums\Compensation as CompensationEnum;
use App\Enums\Deduction as DeductionEnum;
use App\Enums\FormulableComponentSubType;
use App\Enums\IncomeTax as IncomeTaxEnum;
use App\Models\Company;
use App\Models\CompanyFormula;
use App\Models\Formula;

class PayrollComponentServiceConcrete implements PayrollComponentServiceInterface
{
    public ?Company $company = null;

    function setCompany(int|Company $company): void
    {
        $this->company = $company instanceof Company
            ? $company
            : Company::query()->find($company);
    }

    public array $presets = [

        'PH' => [
            'compensation' => [
                [
                    'code' => 'BASICPAY',
                    'name' => 'Basic pay',
                    'assignable' => true,
                    'type' => CompensationEnum::BASIC_PAY,
                    'component_sub_type' => FormulableComponentSubType::BASIC_PAY,
                    'formula_name' => 'Standard-Basic-Pay'
                ], [
                    'code' => 'REGULAR-ALLOWANCE',
                    'name' => 'Regular allowance',
                    'assignable' => true,
                    'type' => CompensationEnum::REGULAR_ALLOWANCE,
                    'component_sub_type' => FormulableComponentSubType::REGULAR_ALLOWANCE,
                    'formula_name' => 'Standard-Allowance'
                ],[
                    'code' => 'MEAL',
                    'name' => 'Meal allowance',
                    'assignable' => true,
                    'type' => CompensationEnum::REGULAR_ALLOWANCE,
                    'component_sub_type' => FormulableComponentSubType::MEAL_ALLOWANCE,
                    'formula_name' => 'Standard-Allowance'
                ],[
                    'code' => 'COFFEE',
                    'name' => 'Coffee allowance',
                    'assignable' => true,
                    'type' => CompensationEnum::REGULAR_ALLOWANCE,
                    'component_sub_type' => FormulableComponentSubType::COFFEE_ALLOWANCE,
                    'formula_name' => 'Standard-Allowance'
                ],[
                    'code' => 'TRANSPORTATION',
                    'name' => 'Transportation allowance',
                    'assignable' => true,
                    'type' => CompensationEnum::REGULAR_ALLOWANCE,
                    'component_sub_type' => FormulableComponentSubType::TRANSPORTATION_ALLOWANCE,
                    'formula_name' => 'Standard-Allowance'
                ],[
                    'code' => 'OVERTIME',
                    'name' => 'Overtime',
                    'assignable' => true,
                    'type' => CompensationEnum::OVERTIME,
                    'component_sub_type' => FormulableComponentSubType::OVERTIME,
                    'formula_name' => 'Standard-Overtime'
                ],[
                    'code' => 'LEAVE-PAY',
                    'name' => 'Leave pay',
                    'assignable' => false,
                    'type' => CompensationEnum::LEAVE_PAY,
                    'component_sub_type' => FormulableComponentSubType::LEAVE_PAY,
                    'formula_name' => 'Standard-Leave-Pay'
                ],[
                    'code' => 'HOLIDAY-PAY',
                    'name' => 'Holiday pay',
                    'assignable' => false,
                    'type' => CompensationEnum::HOLIDAY_PAY,
                    'component_sub_type' => FormulableComponentSubType::HOLIDAY_PAY,
                    'formula_name' => 'Standard-Holiday-Pay'
                ],[
                    'code' => '13THMONTH',
                    'name' => '13th month pay',
                    'assignable' => true,
                    'type' => CompensationEnum::STATUTORY_BENEFIT,
                    'component_sub_type' => FormulableComponentSubType::STATUTORY_BENEFIT_13TH_MONTH,
                    'formula_name' => 'Standard-13th-Month'
                ],
            ],
            'deduction' => [
                [
                    'code' => 'SSS-EMPLOYED',
                    'name' => 'SSS contribution',
                    'assignable' => true,
                    'type' => DeductionEnum::STATUTORY_CONTRIBUTION,
                    'component_sub_type' => FormulableComponentSubType::PH_SSS,
                    'formula_name' => 'Standard-SSS-Employed-Contribution'
                ],[
                    'code' => 'PHILHEALTH',
                    'name' => 'Philhealth (PHIC)',
                    'assignable' => true,
                    'type' => DeductionEnum::STATUTORY_CONTRIBUTION,
                    'component_sub_type' => FormulableComponentSubType::PH_PHILHEALTH,
                    'formula_name' => 'Standard-Philhealth-Contribution'
                ],[
                    'code' => 'PAG-IBIG',
                    'name' => 'Pag-IBIG (HDMF)',
                    'assignable' => true,
                    'type' => DeductionEnum::STATUTORY_CONTRIBUTION,
                    'component_sub_type' => FormulableComponentSubType::PH_PAG_IBIG,
                    'formula_name' => 'Standard-Pag-IBIG-Contribution'
                ],
            ],
            'income_tax' => [
                [
                    'code' => 'WTC',
                    'name' => 'Compensation tax (WTC)',
                    'assignable' => true,
                    'type' => IncomeTaxEnum::WITHHOLDING_TAX,
                    'component_sub_type' => FormulableComponentSubType::PH_WITHHOLDING_TAX_COMPENSATION,
                    'formula_name' => 'Standard-Withholding-Tax-Compensation'
                ],
            ]
        ]
    ];

    public function createDefaults(): void
    {
        $formulas = Formula::all();
        $companyFormulas = CompanyFormula::query()->where('company_id', $this->company->id)->get();
        $iso2 = $this->company->country->iso2;

        foreach ($this->presets[$iso2] as $type => $payrollComponents) {

            foreach ($payrollComponents as $payrollComponent) {

                $formula = $formulas->where('name', $payrollComponent['formula_name'])->first();

                if(empty($formula)) continue;

                $companyFormula = $companyFormulas->where('formula_id', $formula->id)->first();

                if(empty($companyFormula)) continue;

                $payrollComponentRepository = match($type){
                    'compensation'=> CompensationRepository::class,
                    'deduction'=> DeductionRepository::class,
                    'income_tax'=> IncomeTaxRepository::class,
                };

                app($payrollComponentRepository)->store([
                    'company_id' => $this->company->id,
                    ...$payrollComponent,
                    'company_formula_id' => $companyFormula->id
                ]);
            }
        }
    }
}
