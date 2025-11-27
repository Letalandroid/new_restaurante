<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->productName(), // Si no existe, puedes usar ->words(3, true)
            'idCategory' => 1, // O usa ->numberBetween(1, X)
            'details' => $this->faker->optional()->sentence(10),
            'idAlmacen' => 1, // O ->numberBetween(1, X)
            'priceSale' => $this->faker->randomFloat(2, 1, 200),
            'quantityUnitMeasure' => $this->faker->randomFloat(2, 0.1, 10),
            'unitMeasure' => $this->faker->randomElement(['kg', 'g', 'L', 'ml', 'unit']),
            'state' => $this->faker->boolean(),
            'foto' => null,
        ];
    }
}
