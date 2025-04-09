<?php

namespace Database\Seeders;

use App\Enums\CompanyUserAssignmentType;
use App\Models\Account;
use App\Models\Company;
use App\Models\Prototype;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $account = Account::factory()->has(Company::factory()->count(4))
            ->create();

        $superAdmin = User::factory()->superAdmin()
            ->create(['name' => 'kim.123', 'email' => 'luxere20@gmail.com']);

        User::factory(9)->create();

        $superAdmin->companies()
            ->syncWithPivotValues(
                $account->companies->pluck('id')->toArray(),
                ['assignment_type' => CompanyUserAssignmentType::ADMIN]
            );

        Prototype::factory()->count(500)->create();
    }
}
