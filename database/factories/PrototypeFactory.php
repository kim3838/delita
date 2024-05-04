<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Prototype>
 */
class PrototypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                _str_random(3) . '-' . _str_random(5) . '-' . _str_random(3) . '-' . _str_random(6),
                _str_random(6) . '-' . _str_random(3) . '-' . _str_random(2) . '-' . _str_random(6),
                _str_random(3) . '-' . _str_random(3) . '-' . _str_random(5) . '-' . _str_random(6)
            ]),
            'code' => 'PRT' . $this->faker->numerify('######') . $this->faker->numerify('####'),
            'type' => $this->faker->numberBetween(1, 5),
            'category' => $this->faker->randomElement([null, $this->faker->numberBetween(1, 200)]),
            'capacity' => $this->faker->numberBetween(0, 50),
            'datetime_added' => $this->faker->dateTimeBetween('-2 years', '-1 week'),
        ];
    }
}
