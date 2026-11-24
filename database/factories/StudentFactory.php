<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Student> */
class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'admission_number' => strtoupper(fake()->unique()->bothify('STU-####')),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'date_of_birth' => fake()->dateTimeBetween('-25 years', '-16 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'department' => fake()->randomElement(['Computer Science', 'Business Administration', 'Biology']),
            'level' => fake()->randomElement(['100', '200', '300', '400']),
            'status' => 'active',
        ];
    }
}
