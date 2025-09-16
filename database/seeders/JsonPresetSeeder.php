<?php

namespace Database\Seeders;

use App\Models\JsonPreset;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class JsonPresetSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Json Preset Files
        foreach (JsonPreset::timePeriodPresets() as $jsonPreset){

            $filePath = $jsonPreset['resource_preset_path'] . '/' . $jsonPreset['file'];

            Storage::disk('presets')->put('json/time_period/' . $jsonPreset['file'], $this->getJsonContent($filePath));
        }

        foreach (JsonPreset::formulableSettingPresets() as $jsonPreset){

            $filePath = $jsonPreset['resource_preset_path'] . '/' . $jsonPreset['file'];

            Storage::disk('presets')->put('json/formula/' . $jsonPreset['file'], $this->getJsonContent($filePath));
        }

        //Json Presets
        foreach (JsonPreset::allPresets() as $jsonPreset){

            JsonPreset::query()->firstOrCreate([
                'key' => $jsonPreset['key'],
            ], $jsonPreset);
        }
    }

    public function getJsonContent(string $path): false|string
    {
        $path = resource_path($path);

        if (!file_exists($path)) {
            throw new \InvalidArgumentException("JSON file not found: $path");
        }

        return file_get_contents($path);
    }
}
