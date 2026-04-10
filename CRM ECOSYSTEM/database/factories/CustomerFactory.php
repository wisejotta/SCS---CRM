<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition()
    {
        return [
            // 'user_id',
            'country' => fake()->country(),
            'residence' => fake()->country(),
            'dob' => fake()->date(),
            'gender' => rand(0,1) ? 'male' : 'female',
            'phone_number' => fake()->phoneNumber(),
        ];
    }
}
