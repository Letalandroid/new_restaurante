<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('9########'),
            'codigo' => $this->faker->unique()->numerify('########'), // 8 dígitos para DNI
            'client_type_id' => 1, // Ajustar según lo disponible en client_types
            'state' => true,
        ];
    }
}
