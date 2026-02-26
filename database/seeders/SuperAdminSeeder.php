<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->firstOrCreate([
            'name' => 'kim.123',
        ],[
            ...User::factory()->definition(),
            'name' => 'kim.123',
            'type' => UserType::SUPER_ADMIN,
            'email' => 'luxere20@gmail.com',
            'timezone' => 'Asia/Manila',
            'ulid' => Str::ulid(),
        ]);
    }
}
