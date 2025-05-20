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
        $jsonData = [
            'foo' => 'bar',
            'array_value' => [
                'array_value_001',
                'array_value_002',
            ],
            'string_value_key' => 'string',
            'int_value_key' => 123,
            'array_of_key_object' => [
                'key_1' => 'value_1',
                'key_2' => 'value_2',
            ],
            'array_of_objects' => [
                [
                    'value' => 1,
                    'text' => '1'
                ],
                [
                    'value' => 2,
                    'text' => '2'
                ],
                [
                    'value' => 3,
                    'text' => '3'
                ],
            ]
        ];

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
            'json_data' => $jsonData,
            'datetime_added' => $this->faker->dateTimeBetween('-2 years', '-1 week'),
        ];
    }
}
