<?php

namespace Database\Seeders;

use App\Enums\TimePeriodType;
use App\Models\JsonPreset;
use App\Models\TimePeriodPreset;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TimePeriodPresetSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //TimePeriod Presets
        $timePeriodPresets = [
            [
                'type' => TimePeriodType::THIRTEENTH_MONTH,
                'name' => 'november_1st',
                'readable_name' => 'November 1st',
                'yearly_period' => JsonPreset::presetValue('timePeriodPresets', 'yearly_period_november_1st'),
            ],
            [
                'type' => TimePeriodType::PAY_FREQUENCY,
                'name' => 'end_of_month_cut_off',
                'readable_name' => 'End of month',
                'monthly_period' => JsonPreset::presetValue('timePeriodPresets', 'monthly_period_end_of_month_cut_off'),
                'semimonthly_period' => JsonPreset::presetValue('timePeriodPresets', 'semimonthly_period_end_of_month_cut_off'),
            ],
            [
                'type' => TimePeriodType::PAY_FREQUENCY,
                'name' => '10th_cut_off',
                'readable_name' => '10th',
                'monthly_period' => JsonPreset::presetValue('timePeriodPresets', 'monthly_period_10th_cut_off'),
                'semimonthly_period' => JsonPreset::presetValue('timePeriodPresets', 'semimonthly_period_10th_cut_off'),
            ],
            [
                'type' => TimePeriodType::PAY_FREQUENCY,
                'name' => '25th_cut_off',
                'readable_name' => '25th',
                'monthly_period' => JsonPreset::presetValue('timePeriodPresets', 'monthly_period_25th_cut_off'),
                'semimonthly_period' => JsonPreset::presetValue('timePeriodPresets', 'semimonthly_period_25th_cut_off'),
            ],
            [
                'type' => TimePeriodType::NIGHT_DIFFERENTIAL_HOURS,
                'name' => 'night_differential_hours',
                'readable_name' => 'Night Differential Hours',
                'hour_period' => JsonPreset::presetValue('timePeriodPresets', 'night_differential_hours'),
            ]
        ];

        foreach ($timePeriodPresets as $timePeriodPreset) {
            TimePeriodPreset::create($timePeriodPreset);
        }
    }
}
