<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Timezone;
use Illuminate\Database\Seeder;

class WorldSeeder extends Seeder
{
    public string $countryResourceFilePath = 'presets/json/world/country.json';
    public string $timezoneResourceFilePath = 'presets/json/world/timezone.json';
    public string $currencyResourceFilePath = 'presets/json/world/currency.json';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = $this->getJsonContent($this->countryResourceFilePath);
        $timezones = $this->getJsonContent($this->timezoneResourceFilePath);
        $currencies = $this->getJsonContent($this->currencyResourceFilePath);

        foreach (json_decode($countries, true) as $country) {

            Country::query()->firstOrCreate([
                'iso2' => $country['iso2'],
            ], $country);
        }

        foreach (json_decode($timezones, true) as $timezone) {

            Timezone::query()->firstOrCreate([
                'name' => $timezone['name'],
            ], $timezone);
        }

        foreach (json_decode($currencies, true) as $currency) {

            Currency::query()->firstOrCreate([
                'name' => $currency['name'],
                'code' => $currency['code'],
            ], $currency);
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
