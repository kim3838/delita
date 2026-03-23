<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(WorldSeeder::class);
        $this->call(JsonPresetSeeder::class);
        $this->call(TimePeriodPresetSeeder::class);
        $this->call(FormulaSeeder::class);
        $this->call(SuperAdminSeeder::class);
        $this->call(PermissionAndRoleSeeder::class);
        $this->call(ApprovalSettingSeeder::class);

        if(App::environment('development', 'staging')){
            $this->call(Development::class);
        }
    }
}
