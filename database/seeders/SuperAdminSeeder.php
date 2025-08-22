<?php

namespace Database\Seeders;

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
        User::factory()->superAdmin()->create([
            'name' => 'kim.123',
            'email' => 'luxere20@gmail.com',
            'timezone' => 'Asia/Manila',
            'ulid' => Str::ulid()
        ]);
    }
}
