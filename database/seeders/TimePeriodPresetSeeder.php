<?php

namespace Database\Seeders;

use App\Enums\TimePeriodType;
use App\Models\JsonPreset;
use App\Models\TimePeriodPreset;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

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
                'yearly_period' => Storage::disk('presets')->json(JsonPreset::where('key', 'yearly_period_november_1st')->first()->path)
            ],
            [
                'type' => TimePeriodType::PAY_FREQUENCY,
                'name' => 'end_of_month_cut_off',
                'readable_name' => 'End of month',
                'monthly_period' => Storage::disk('presets')->json(JsonPreset::where('key', 'monthly_period_end_of_month_cut_off')->first()->path),
                'semimonthly_period' => Storage::disk('presets')->json(JsonPreset::where('key', 'semimonthly_period_end_of_month_cut_off')->first()->path),
            ],
            [
                'type' => TimePeriodType::PAY_FREQUENCY,
                'name' => '05th_cut_off',
                'readable_name' => '05th',
                'monthly_period' => Storage::disk('presets')->json(JsonPreset::where('key', 'monthly_period_05th_cut_off')->first()->path),
                'semimonthly_period' => Storage::disk('presets')->json(JsonPreset::where('key', 'semimonthly_period_05th_cut_off')->first()->path),
            ],
            [
                'type' => TimePeriodType::PAY_FREQUENCY,
                'name' => '10th_cut_off',
                'readable_name' => '10th',
                'monthly_period' => Storage::disk('presets')->json(JsonPreset::where('key', 'monthly_period_10th_cut_off')->first()->path),
                'semimonthly_period' => Storage::disk('presets')->json(JsonPreset::where('key', 'semimonthly_period_10th_cut_off')->first()->path),
            ],
            [
                'type' => TimePeriodType::PAY_FREQUENCY,
                'name' => '20th_cut_off',
                'readable_name' => '20th',
                'monthly_period' => Storage::disk('presets')->json(JsonPreset::where('key', 'monthly_period_20th_cut_off')->first()->path),
                'semimonthly_period' => Storage::disk('presets')->json(JsonPreset::where('key', 'semimonthly_period_20th_cut_off')->first()->path),
            ],
            [
                'type' => TimePeriodType::PAY_FREQUENCY,
                'name' => '25th_cut_off',
                'readable_name' => '25th',
                'monthly_period' => Storage::disk('presets')->json(JsonPreset::where('key', 'monthly_period_25th_cut_off')->first()->path),
                'semimonthly_period' => Storage::disk('presets')->json(JsonPreset::where('key', 'semimonthly_period_25th_cut_off')->first()->path),
            ],
            [
                'type' => TimePeriodType::NIGHT_DIFFERENTIAL_HOURS,
                'name' => 'night_differential_hours',
                'readable_name' => 'Night Differential Hours',
                'hour_period' => Storage::disk('presets')->json(JsonPreset::where('key', 'night_differential_hours')->first()->path),
            ]
        ];

        foreach ($timePeriodPresets as $timePeriodPreset) {

            TimePeriodPreset::query()->firstOrCreate([
                'name' => $timePeriodPreset['name'],
            ], $timePeriodPreset);
        }
    }
}
