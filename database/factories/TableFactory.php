<?php

namespace Database\Factories;

use App\Models\Table;
use App\Models\Areas;
use App\Models\Floor;
use Illuminate\Database\Eloquent\Factories\Factory;

class TableFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Table::class;

    protected static int $counter = 1; // Contador estático para numerar las mesas

    public function definition(): array
    {
        return [
            'name'      => 'Mesa ' . self::$counter++, // Genera Mesa 1, Mesa 2, etc.
            'tablenum'  => 'TBL-' . $this->faker->unique()->numberBetween(1, 999),
            
            //CORREGIDO: usar closure para evitar inicialización temprana
            'idArea'    => fn() => Areas::inRandomOrder()->value('id'),
            'idFloor'   => fn() => Floor::inRandomOrder()->value('id'),

            'capacity'  => $this->faker->numberBetween(2, 10),
            'state'     => $this->faker->boolean(70), // 80% activo
        ];
    }
}
