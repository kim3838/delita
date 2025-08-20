<?php

namespace App\Concrete;

use App\Enums\Compensation;
use App\Enums\Deduction;
use App\Enums\Formulable;
use App\Enums\IncomeTax;

class JsonPresets
{
    public static function timePeriodPresets(): array
    {
        return [
            [
                'key' => 'yearly_period_november_1st',
                'value' => read_json('time_period_preset/01.yearly_period_november_1st.json')
            ],
            [
                'key' => 'monthly_period_end_of_month_cut_off',
                'value' => read_json('time_period_preset/02.monthly_period_end_of_month_cut_off.json'),
            ],
            [
                'key' => 'semimonthly_period_end_of_month_cut_off',
                'value' => read_json('time_period_preset/03.semimonthly_period_end_of_month_cut_off.json'),
            ],
            [
                'key' => 'monthly_period_10th_cut_off',
                'value' => read_json('time_period_preset/04.monthly_period_10th_cut_off.json'),
            ],
            [
                'key' => 'semimonthly_period_10th_cut_off',
                'value' => read_json('time_period_preset/05.semimonthly_period_10th_cut_off.json'),
            ],
            [
                'key' => 'monthly_period_25th_cut_off',
                'value' => read_json('time_period_preset/06.monthly_period_25th_cut_off.json'),
            ],
            [
                'key' => 'semimonthly_period_25th_cut_off',
                'value' => read_json('time_period_preset/07.semimonthly_period_25th_cut_off.json'),
            ],
            [
                'key' => 'night_differential_hours',
                'value' => read_json('time_period_preset/08.night_differential_hours.json'),
            ]
        ];
    }

    public static function formulableSettingPresets(): array
    {
        return [
            [
                'key' => 'standard_basic_salary',
                'formulable_type' => Formulable::EARNINGS,
                'component_type' => Compensation::BASIC_SALARY,
                'value' => read_json('formula_preset/001.standard.earnings.01.basic-salary.json'),
            ],
            [
                'key' => 'standard_overtime',
                'formulable_type' => Formulable::EARNINGS,
                'component_type' => Compensation::OVERTIME,
                'value' => read_json('formula_preset/001.standard.earnings.02.overtime.json'),
            ],
            [
                'key' => 'standard_meal',
                'formulable_type' => Formulable::EARNINGS,
                'component_type' => Compensation::REGULAR_ALLOWANCE,
                'value' => read_json('formula_preset/001.standard.earnings.03.meal-allowance.json'),
            ],
            [
                'key' => 'standard_13th_month',
                'formulable_type' => Formulable::EARNINGS,
                'component_type' => Compensation::BENEFIT,
                'value' => read_json('formula_preset/001.standard.earnings.04.13th-month.json'),
            ],
            [
                'key' => 'standard_tardiness',
                'formulable_type' => Formulable::DEDUCTIONS,
                'component_type' => Deduction::DEDUCTION,
                'value' => read_json('formula_preset/002.standard.deductions.01.tardiness.json'),
            ],
            [
                'key' => 'standard_absence',
                'formulable_type' => Formulable::DEDUCTIONS,
                'component_type' => Deduction::DEDUCTION,
                'value' => read_json('formula_preset/002.standard.deductions.02.absence.json'),
            ],
            [
                'key' => 'standard_sss_employed_contribution',
                'formulable_type' => Formulable::DEDUCTIONS,
                'component_type' => Deduction::CONTRIBUTION,
                'value' => read_json('formula_preset/002.standard.deductions.03.sss-employed.json'),
            ],
            [
                'key' => 'standard_philhealth_contribution',
                'formulable_type' => Formulable::DEDUCTIONS,
                'component_type' => Deduction::CONTRIBUTION,
                'value' => read_json('formula_preset/002.standard.deductions.04.philhealth.json'),
            ],
            [
                'key' => 'standard_pagibig_contribution',
                'formulable_type' => Formulable::DEDUCTIONS,
                'component_type' => Deduction::CONTRIBUTION,
                'value' => read_json('formula_preset/002.standard.deductions.05.pagibig.json'),
            ],
            [
                'key' => 'standard_compensation_tax',
                'formulable_type' => Formulable::INCOME_TAX,
                'component_type' => IncomeTax::COMPENSATION_TAX,
                'value' => read_json('formula_preset/005.standard.compensation-tax.01.compensation-tax.json'),
            ],
        ];
    }

    public static function presetValue($preset, $key)
    {
        // Check if the method exists
        if (!method_exists(self::class, $preset)) {
            throw new \InvalidArgumentException("Method {$preset} does not exist");
        }

        // Call the method dynamically using call_user_func
        $presets = call_user_func([self::class, $preset]);

        return collect($presets)->firstWhere('key', $key)['value'];
    }
}
