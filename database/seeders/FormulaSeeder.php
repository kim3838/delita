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
use Illuminate\Support\Facades\Storage;
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
            ['name' => 'Standard-Basic-Salary', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::BASIC_SALARY, 'aggregation' => false,
                'default_settings' => Storage::disk('presets')->json(JsonPreset::where('key', 'standard_basic_salary')->first()->path)
            ],
            ['name' => 'Standard-Allowance', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::REGULAR_ALLOWANCE, 'aggregation' => false,
                'default_settings' => Storage::disk('presets')->json(JsonPreset::where('key', 'standard_allowance')->first()->path)
            ],
            ['name' => 'Standard-Overtime', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::OVERTIME, 'aggregation' => false,
                'default_settings' => Storage::disk('presets')->json(JsonPreset::where('key', 'standard_overtime')->first()->path)
            ],
            ['name' => 'Standard-Leave-Pay', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::LEAVE_PAY, 'aggregation' => false,
                'default_settings' => Storage::disk('presets')->json(JsonPreset::where('key', 'standard_leave_pay')->first()->path)
            ],
            ['name' => 'Standard-Holiday-Pay', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::HOLIDAY_PAY, 'aggregation' => false,
                'default_settings' => Storage::disk('presets')->json(JsonPreset::where('key', 'standard_holiday_pay')->first()->path)
            ],
            ['name' => 'Standard-13th-Month', 'formulable_type' => Formulable::EARNINGS ,'component_type' => Compensation::BENEFIT, 'aggregation' => false,
                'default_settings' => Storage::disk('presets')->json(JsonPreset::where('key', 'standard_13th_month')->first()->path)
            ],

            //Deductions
            ['name' => 'Standard-Tardiness', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::DEDUCTION, 'aggregation' => false,
                'default_settings' => Storage::disk('presets')->json(JsonPreset::where('key', 'standard_tardiness')->first()->path)
            ],
            ['name' => 'Standard-Undertime', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::DEDUCTION, 'aggregation' => false,
                'default_settings' => Storage::disk('presets')->json(JsonPreset::where('key', 'standard_undertime')->first()->path)
            ],
            ['name' => 'Standard-Absence', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::DEDUCTION, 'aggregation' => false,
                'default_settings' => Storage::disk('presets')->json(JsonPreset::where('key', 'standard_absence')->first()->path)
            ],
            ['name' => 'Standard-SSS-Employed-Contribution', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::CONTRIBUTION, 'aggregation' => false,
                'default_settings' => Storage::disk('presets')->json(JsonPreset::where('key', 'standard_sss_employed_contribution')->first()->path)
            ],
            ['name' => 'Standard-Philhealth-Contribution', 'formulable_type' => Formulable::DEDUCTIONS  ,'component_type' => Deduction::CONTRIBUTION, 'aggregation' => false,
                'default_settings' => Storage::disk('presets')->json(JsonPreset::where('key', 'standard_philhealth_contribution')->first()->path)
            ],
            ['name' => 'Standard-Pagibig-Contribution', 'formulable_type' => Formulable::DEDUCTIONS ,'component_type' => Deduction::CONTRIBUTION, 'aggregation' => false,
                'default_settings' => Storage::disk('presets')->json(JsonPreset::where('key', 'standard_pagibig_contribution')->first()->path)
            ],

            //Taxable Income
            ['name' => 'Standard-Taxable-Income', 'formulable_type' => Formulable::TAXABLE_INCOME ,'component_type' => null, 'aggregation' => true],

            //Non-taxable Income
            ['name' => 'Standard-Nontaxable-Income', 'formulable_type' => Formulable::NONTAXABLE_INCOME ,'component_type' => null, 'aggregation' => true],

            //Income Tax
            ['name' => 'Standard-Compensation-Tax', 'formulable_type' => Formulable::INCOME_TAX ,'component_type' => IncomeTax::COMPENSATION_TAX, 'aggregation' => false,
                'default_settings' => Storage::disk('presets')->json(JsonPreset::where('key', 'standard_compensation_tax')->first()->path)
            ],

            //Net Income
            ['name' => 'Standard-Net-Income', 'formulable_type' => Formulable::NET_INCOME ,'component_type' => null, 'aggregation' => true]
        ];

        foreach ($formulaPresets as $formula) {

            Formula::query()->firstOrCreate([
                'name' => $formula['name'],
            ], ['ulid' => Str::ulid(), ...$formula]);
        }
    }
}
