<?php

namespace Database\Factories;

use App\Models\Areas;
use App\Models\Floor;
use Illuminate\Database\Eloquent\Factories\Factory;

class TableFactory extends Factory
{
    protected static int $counter = 1; // Contador estático para numerar las mesas

    public function definition(): array
    {
        return [
            'name'      => 'Mesa ' . self::$counter++, // Genera Mesa 1, Mesa 2, Mesa 3, etc.
            'tablenum'  => $this->faker->unique()->numerify('TBL-###'),
            'idArea'    => Areas::inRandomOrder()->first()->id,
            'idFloor'   => Floor::inRandomOrder()->first()->id,
            'capacity'  => $this->faker->numberBetween(2, 10),
            'state'     => $this->faker->boolean(70), // 80% activo
        ];
    }
}
