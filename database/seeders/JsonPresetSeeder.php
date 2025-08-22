<?php

namespace Database\Seeders;

use App\Models\JsonPreset;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JsonPresetSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Json Presets
        foreach (JsonPreset::allPresets() as $jsonPreset){
            JsonPreset::create($jsonPreset);
        }
    }
}
