<?php

namespace Database\Seeders;

use App\Enums\Compensation;
use App\Enums\Deduction;
use App\Enums\Formulable;
use App\Enums\IncomeTax;
use App\Models\Formula;
use App\Models\JsonPreset;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FormulaSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Formula Presets
        $formulaPresets = [
            //Earnings
            ['name' => 'Standard-Salary', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::BASIC_SALARY, 'interpolation' => false,
                'default_settings' => JsonPreset::presetValue('formulableSettingPresets', 'standard_basic_salary')
            ],
            ['name' => 'Standard-Overtime', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::OVERTIME, 'interpolation' => false,
                'default_settings' => JsonPreset::presetValue('formulableSettingPresets', 'standard_overtime')
            ],
            ['name' => 'Standard-Meal', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::REGULAR_ALLOWANCE, 'interpolation' => false,
                'default_settings' => JsonPreset::presetValue('formulableSettingPresets', 'standard_meal')
            ],
            ['name' => 'Standard-13th-Month', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::BENEFIT, 'interpolation' => false,
                'default_settings' => JsonPreset::presetValue('formulableSettingPresets', 'standard_13th_month')
            ],

            //Deductions
            ['name' => 'Standard-Tardiness', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::DEDUCTION, 'interpolation' => false,
                'default_settings' => JsonPreset::presetValue('formulableSettingPresets', 'standard_tardiness')
            ],
            ['name' => 'Standard-Absence', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::DEDUCTION, 'interpolation' => false,
                'default_settings' => JsonPreset::presetValue('formulableSettingPresets', 'standard_absence')
            ],
            ['name' => 'Standard-SSS-Employed-Contribution', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::CONTRIBUTION, 'interpolation' => false,
                'default_settings' => JsonPreset::presetValue('formulableSettingPresets', 'standard_sss_employed_contribution')
            ],
            ['name' => 'Standard-Philhealth-Contribution', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::CONTRIBUTION, 'interpolation' => false,
                'default_settings' => JsonPreset::presetValue('formulableSettingPresets', 'standard_philhealth_contribution')
            ],
            ['name' => 'Standard-Pagibig-Contribution', 'formulable_type' => Formulable::DEDUCTIONS ,'component_type' => Deduction::CONTRIBUTION, 'interpolation' => false,
                'default_settings' => JsonPreset::presetValue('formulableSettingPresets', 'standard_pagibig_contribution')
            ],

            //Taxable Income
            ['name' => 'Standard-Taxable-Income', 'formulable_type' => Formulable::TAXABLE_INCOME ,'component_type' => null, 'interpolation' => true],

            //Non-taxable Income
            ['name' => 'Standard-Nontaxable-Income', 'formulable_type' => Formulable::NONTAXABLE_INCOME ,'component_type' => null, 'interpolation' => true],

            //Income Tax
            ['name' => 'Standard-Compensation-Tax', 'formulable_type' => Formulable::INCOME_TAX ,'component_type' => IncomeTax::COMPENSATION_TAX, 'interpolation' => false,
                'default_settings' => JsonPreset::presetValue('formulableSettingPresets', 'standard_compensation_tax')
            ],

            //Net Income
            ['name' => 'Standard-Net-Income', 'formulable_type' => Formulable::NET_INCOME ,'component_type' => null, 'interpolation' => true]
        ];

        foreach ($formulaPresets as $formula) {
            Formula::create(['ulid' => Str::ulid(), ...$formula]);
        }
    }
}
