<?php

namespace Database\Seeders;

use App\Enums\UserType;
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
        User::factory()->create([
            'name' => 'kim.123',
            'email' => 'luxere20@gmail.com',
            'type' => UserType::SUPER_ADMIN
        ]);

        User::factory(9)->create();

        Prototype::factory()->count(500)->create();
    }
}
