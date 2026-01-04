<?php

namespace Database\Factories;

use App\Enums\AccountSubscriptionPlan;
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
            'plan' => AccountSubscriptionPlan::STANDARD
        ];
    }

    public function standard(): Factory|AccountFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'plan' => AccountSubscriptionPlan::STANDARD
            ];
        });
    }

    public function business(): Factory|AccountFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'plan' => AccountSubscriptionPlan::BUSINESS
            ];
        });
    }
}
