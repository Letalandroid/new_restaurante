<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->jobTitle(),
            'payment_type' => $this->faker->randomElement(['fijo', 'por_hora']),
            'base_salary' => fn() => $this->faker->randomElement([null, $this->faker->numberBetween(1000, 3000)]),
            'hourly_rate' => fn() => $this->faker->randomElement([null, $this->faker->randomFloat(2, 8, 20)]),
            'has_punctuality_bonus' => $this->faker->boolean(),
            'punctuality_bonus' => fn($attributes) => $attributes['has_punctuality_bonus'] ? $this->faker->numberBetween(20, 200) : null,
            'state' => $this->faker->boolean(),
        ];
    }
}
