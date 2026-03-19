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

            $resourceFilePath = $jsonPreset['resource_preset_path'] . '/' . $jsonPreset['file'];

            $diskFilePath = 'config/json/time_period/' . $jsonPreset['file'];

            if(Storage::missing($diskFilePath)){

                Storage::put($diskFilePath, $this->getJsonContent($resourceFilePath));
            }
        }

        foreach (JsonPreset::formulableSettingPresets() as $jsonPreset){

            $resourceFilePath = $jsonPreset['resource_preset_path'] . '/' . $jsonPreset['file'];

            $diskFilePath = 'config/json/formula/' . $jsonPreset['file'];

            if(Storage::missing($diskFilePath)){

                Storage::put($diskFilePath, $this->getJsonContent($resourceFilePath));
            }
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
