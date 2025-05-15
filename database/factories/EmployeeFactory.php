<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $employeeNumber = str_pad(
            $this->faker->numberBetween(0, 9999),
            6,
            '0',
            STR_PAD_LEFT
        );

        return [
            'user_id' => User::find(1)->id,
            'company_id' => Company::find(1)->id,
            'given_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->randomElement([null, $this->faker->lastName()]),
            'family_name' => $this->faker->lastName(),
            'birth_date' => $this->faker->dateTimeBetween('-40 years', '-21 years'),
            'gender' => $this->faker->randomElement([
                Gender::NOT_SPECIFIED,
                Gender::MALE,
                Gender::FEMALE,
            ]),
            'marital_status' => $this->faker->randomElement([
                MaritalStatus::NOT_SPECIFIED,
                MaritalStatus::SINGLE,
                MaritalStatus::MARRIED,
            ]),
            'date_registered' => $this->faker->dateTimeBetween('-5 years', '-1 week'),
        ];
    }
}
