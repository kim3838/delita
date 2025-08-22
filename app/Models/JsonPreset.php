<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JsonPreset extends Model
{
    protected $fillable = [
        'key',
        'resource_path',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'id' => 'int',
        'key' => 'string',
        'resource_path' => 'string',
    ];

    public static function timePeriodPresets(): array
    {
        return [
            [
                'key' => 'yearly_period_november_1st',
                'resource_path' => 'time_period_preset/01.yearly_period_november_1st.json',
                'value' => read_json('time_period_preset/01.yearly_period_november_1st.json')
            ],
            [
                'key' => 'monthly_period_end_of_month_cut_off',
                'resource_path' => 'time_period_preset/02.monthly_period_end_of_month_cut_off.json',
                'value' => read_json('time_period_preset/02.monthly_period_end_of_month_cut_off.json'),
            ],
            [
                'key' => 'semimonthly_period_end_of_month_cut_off',
                'resource_path' => 'time_period_preset/03.semimonthly_period_end_of_month_cut_off.json',
                'value' => read_json('time_period_preset/03.semimonthly_period_end_of_month_cut_off.json'),
            ],
            [
                'key' => 'monthly_period_10th_cut_off',
                'resource_path' => 'time_period_preset/04.monthly_period_10th_cut_off.json',
                'value' => read_json('time_period_preset/04.monthly_period_10th_cut_off.json'),
            ],
            [
                'key' => 'semimonthly_period_10th_cut_off',
                'resource_path' => 'time_period_preset/05.semimonthly_period_10th_cut_off.json',
                'value' => read_json('time_period_preset/05.semimonthly_period_10th_cut_off.json'),
            ],
            [
                'key' => 'monthly_period_25th_cut_off',
                'resource_path' => 'time_period_preset/06.monthly_period_25th_cut_off.json',
                'value' => read_json('time_period_preset/06.monthly_period_25th_cut_off.json'),
            ],
            [
                'key' => 'semimonthly_period_25th_cut_off',
                'resource_path' => 'time_period_preset/07.semimonthly_period_25th_cut_off.json',
                'value' => read_json('time_period_preset/07.semimonthly_period_25th_cut_off.json'),
            ],
            [
                'key' => 'night_differential_hours',
                'resource_path' => 'time_period_preset/08.night_differential_hours.json',
                'value' => read_json('time_period_preset/08.night_differential_hours.json'),
            ]
        ];
    }

    public static function formulableSettingPresets(): array
    {
        return [
            [
                'key' => 'standard_basic_salary',
                'resource_path' => 'formula_preset/001.standard.earnings.01.basic-salary.json',
                'value' => read_json('formula_preset/001.standard.earnings.01.basic-salary.json'),
            ],
            [
                'key' => 'standard_overtime',
                'resource_path' => 'formula_preset/001.standard.earnings.02.overtime.json',
                'value' => read_json('formula_preset/001.standard.earnings.02.overtime.json'),
            ],
            [
                'key' => 'standard_meal',
                'resource_path' => 'formula_preset/001.standard.earnings.03.meal-allowance.json',
                'value' => read_json('formula_preset/001.standard.earnings.03.meal-allowance.json'),
            ],
            [
                'key' => 'standard_13th_month',
                'resource_path' => 'formula_preset/001.standard.earnings.04.13th-month.json',
                'value' => read_json('formula_preset/001.standard.earnings.04.13th-month.json'),
            ],
            [
                'key' => 'standard_tardiness',
                'resource_path' => 'formula_preset/002.standard.deductions.01.tardiness.json',
                'value' => read_json('formula_preset/002.standard.deductions.01.tardiness.json'),
            ],
            [
                'key' => 'standard_absence',
                'resource_path' => 'formula_preset/002.standard.deductions.02.absence.json',
                'value' => read_json('formula_preset/002.standard.deductions.02.absence.json'),
            ],
            [
                'key' => 'standard_sss_employed_contribution',
                'resource_path' => 'formula_preset/002.standard.deductions.03.sss-employed.json',
                'value' => read_json('formula_preset/002.standard.deductions.03.sss-employed.json'),
            ],
            [
                'key' => 'standard_philhealth_contribution',
                'resource_path' => 'formula_preset/002.standard.deductions.04.philhealth.json',
                'value' => read_json('formula_preset/002.standard.deductions.04.philhealth.json'),
            ],
            [
                'key' => 'standard_pagibig_contribution',
                'resource_path' => 'formula_preset/002.standard.deductions.05.pagibig.json',
                'value' => read_json('formula_preset/002.standard.deductions.05.pagibig.json'),
            ],
            [
                'key' => 'standard_compensation_tax',
                'resource_path' => 'formula_preset/005.standard.compensation-tax.01.compensation-tax.json',
                'value' => read_json('formula_preset/005.standard.compensation-tax.01.compensation-tax.json'),
            ],
        ];
    }

    public static function allPresets(): array
    {
        return array_map(function($preset){
            return [
                'key' => $preset['key'],
                'resource_path' => $preset['resource_path'],
            ];
        },array_merge(
            self::timePeriodPresets(),
            self::formulableSettingPresets()
        ));
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
