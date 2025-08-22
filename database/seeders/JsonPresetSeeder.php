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

            Storage::disk('presets')->put('json/time_period/' . $jsonPreset['file'], json_encode($this->readJson($filePath)));
        }

        foreach (JsonPreset::formulableSettingPresets() as $jsonPreset){

            $filePath = $jsonPreset['resource_preset_path'] . '/' . $jsonPreset['file'];

            Storage::disk('presets')->put('json/formula/' . $jsonPreset['file'], json_encode($this->readJson($filePath)));
        }

        //Json Presets
        foreach (JsonPreset::allPresets() as $jsonPreset){
            JsonPreset::create($jsonPreset);
        }
    }

    public function readJson(string $path): array
    {
        $path = resource_path($path);

        if (!file_exists($path)) {
            throw new \InvalidArgumentException("JSON file not found: $path");
        }

        return json_decode(file_get_contents($path), true) ?? [];
    }
}
