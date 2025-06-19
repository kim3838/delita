<?php

namespace Database\Factories;

use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => $this->faker->numberBetween(1000, 1100),
            'type' => AccountType::STANDARD
        ];
    }

    public function standard(): Factory|AccountFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => AccountType::STANDARD
            ];
        });
    }

    public function corporate(): Factory|AccountFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => AccountType::CORPORATE
            ];
        });
    }
}
