<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JsonPreset extends Model
{
    protected $fillable = [
        'key',
        'disk',
        'path',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'id' => 'int',
        'key' => 'string',
        'disk' => 'string',
        'path' => 'string',
    ];

    public static function timePeriodPresets(): array
    {
        return [
            [
                'key' => 'yearly_period_november_1st',
                'resource_preset_path' => 'presets/json/time_period',
                'file' => '01.yearly_period_november_1st.json'
            ],
            [
                'key' => 'monthly_period_end_of_month_cut_off',
                'resource_preset_path' => 'presets/json/time_period',
                'file' => '02.monthly_period_end_of_month_cut_off.json',
            ],
            [
                'key' => 'semimonthly_period_end_of_month_cut_off',
                'resource_preset_path' => 'presets/json/time_period',
                'file' => '03.semimonthly_period_end_of_month_cut_off.json',
            ],
            [
                'key' => 'monthly_period_05th_cut_off',
                'resource_preset_path' => 'presets/json/time_period',
                'file' => '04.monthly_period_05th_cut_off.json',
            ],
            [
                'key' => 'semimonthly_period_05th_cut_off',
                'resource_preset_path' => 'presets/json/time_period',
                'file' => '05.semimonthly_period_05th_cut_off.json',
            ],
            [
                'key' => 'monthly_period_10th_cut_off',
                'resource_preset_path' => 'presets/json/time_period',
                'file' => '06.monthly_period_10th_cut_off.json',
            ],
            [
                'key' => 'semimonthly_period_10th_cut_off',
                'resource_preset_path' => 'presets/json/time_period',
                'file' => '07.semimonthly_period_10th_cut_off.json',
            ],
            [
                'key' => 'monthly_period_20th_cut_off',
                'resource_preset_path' => 'presets/json/time_period',
                'file' => '08.monthly_period_20th_cut_off.json',
            ],
            [
                'key' => 'semimonthly_period_20th_cut_off',
                'resource_preset_path' => 'presets/json/time_period',
                'file' => '09.semimonthly_period_20th_cut_off.json',
            ],
            [
                'key' => 'monthly_period_25th_cut_off',
                'resource_preset_path' => 'presets/json/time_period',
                'file' => '10.monthly_period_25th_cut_off.json',
            ],
            [
                'key' => 'semimonthly_period_25th_cut_off',
                'resource_preset_path' => 'presets/json/time_period',
                'file' => '11.semimonthly_period_25th_cut_off.json',
            ],
            [
                'key' => 'night_differential_hours',
                'resource_preset_path' => 'presets/json/time_period',
                'file' => '12.night_differential_hours.json',
            ]
        ];
    }

    public static function formulableSettingPresets(): array
    {
        return [
            [
                'key' => 'standard_basic_pay',
                'resource_preset_path' => 'presets/json/formula',
                'file' => '001.standard.earnings.01.basic-pay.json'
            ],
            [
                'key' => 'standard_allowance',
                'resource_preset_path' => 'presets/json/formula',
                'file' => '001.standard.earnings.02.regular-allowance.json',
            ],
            [
                'key' => 'standard_overtime',
                'resource_preset_path' => 'presets/json/formula',
                'file' => '001.standard.earnings.03.overtime.json',
            ],
            [
                'key' => 'standard_leave_pay',
                'resource_preset_path' => 'presets/json/formula',
                'file' => '001.standard.earnings.04.leave-pay.json',
            ],
            [
                'key' => 'standard_holiday_pay',
                'resource_preset_path' => 'presets/json/formula',
                'file' => '001.standard.earnings.05.holiday-pay.json',
            ],
            [
                'key' => 'standard_13th_month',
                'resource_preset_path' => 'presets/json/formula',
                'file' => '001.standard.earnings.06.13th-month.json',
            ],
            [
                'key' => 'standard_tardiness',
                'resource_preset_path' => 'presets/json/formula',
                'file' => '002.standard.deductions.01.tardiness.json',
            ],
            [
                'key' => 'standard_undertime',
                'resource_preset_path' => 'presets/json/formula',
                'file' => '002.standard.deductions.02.undertime.json',
            ],
            [
                'key' => 'standard_absence',
                'resource_preset_path' => 'presets/json/formula',
                'file' => '002.standard.deductions.03.absence.json',
            ],
            [
                'key' => 'standard_sss_employed_contribution',
                'resource_preset_path' => 'presets/json/formula',
                'file' => '002.standard.deductions.04.sss-employed.json',
            ],
            [
                'key' => 'standard_philhealth_contribution',
                'resource_preset_path' => 'presets/json/formula',
                'file' => '002.standard.deductions.05.philhealth.json',
            ],
            [
                'key' => 'standard_pag_ibig_contribution',
                'resource_preset_path' => 'presets/json/formula',
                'file' => '002.standard.deductions.06.pag-ibig.json',
            ],
            [
                'key' => 'standard_withholding_tax_compensation',
                'resource_preset_path' => 'presets/json/formula',
                'file' => '003.standard.income-tax.01.withholding-tax-compensation.json',
            ],
        ];
    }

    public static function allPresets(): array
    {
        return array_merge(
            array_map(function($preset){
                return [
                    'key' => $preset['key'],
                    'disk' => 'presets',
                    'path' => 'json/time_period/' . $preset['file'],
                ];
            }, self::timePeriodPresets()),

            array_map(function($preset){
                return [
                    'key' => $preset['key'],
                    'disk' => 'presets',
                    'path' => 'json/formula/' . $preset['file'],
                ];
            }, self::formulableSettingPresets()),

        );
    }
}
