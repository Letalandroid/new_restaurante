<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Input;
use App\Models\Almacen;
use App\Models\Supplier;

class InputSeeder extends Seeder
{
    public function run(): void
    {
        $inputs = [
            [
                'name' => 'Aceite de oliva',
                'description' => 'Aceite de oliva extra virgen para uso culinario.',
                'idAlmacen' => 1,
                'priceBuy' => 12.50,
                'priceSale' => 15.00,
                'quantityUnitMeasure' => 1,
                'unitMeasure' => 'litros',
            ],
            [
                'name' => 'Harina de trigo',
                'description' => 'Harina de trigo refinada para panadería.',
                'idAlmacen' => 1,
                'priceBuy' => 3.00,
                'priceSale' => 4.20,
                'quantityUnitMeasure' => 1,
                'unitMeasure' => 'kg',
            ],
            [
                'name' => 'Azúcar',
                'description' => 'Azúcar rubia granulada.',
                'idAlmacen' => 2,
                'priceBuy' => 2.80,
                'priceSale' => 3.50,
                'quantityUnitMeasure' => 1,
                'unitMeasure' => 'kg',
            ],
            [
                'name' => 'Sal',
                'description' => 'Sal de mesa fina.',
                'idAlmacen' => 2,
                'priceBuy' => 1.00,
                'priceSale' => 1.50,
                'quantityUnitMeasure' => 500,
                'unitMeasure' => 'g',
            ],
            [
                'name' => 'Pimienta negra',
                'description' => 'Pimienta negra molida.',
                'idAlmacen' => 2,
                'priceBuy' => 2.50,
                'priceSale' => 3.20,
                'quantityUnitMeasure' => 100,
                'unitMeasure' => 'g',
            ],
            [
                'name' => 'Tomate',
                'description' => 'Tomate fresco de primera calidad.',
                'idAlmacen' => 1,
                'priceBuy' => 1.80,
                'priceSale' => 2.20,
                'quantityUnitMeasure' => 1,
                'unitMeasure' => 'kg',
            ],
            [
                'name' => 'Cebolla',
                'description' => 'Cebolla roja fresca.',
                'idAlmacen' => 1,
                'priceBuy' => 1.60,
                'priceSale' => 2.10,
                'quantityUnitMeasure' => 1,
                'unitMeasure' => 'kg',
            ],
            [
                'name' => 'Ajo',
                'description' => 'Ajo fresco entero.',
                'idAlmacen' => 1,
                'priceBuy' => 0.80,
                'priceSale' => 1.20,
                'quantityUnitMeasure' => 100,
                'unitMeasure' => 'g',
            ],
            [
                'name' => 'Leche',
                'description' => 'Leche fresca pasteurizada.',
                'idAlmacen' => 2,
                'priceBuy' => 3.20,
                'priceSale' => 4.00,
                'quantityUnitMeasure' => 1,
                'unitMeasure' => 'litros',
            ],
            [
                'name' => 'Huevos',
                'description' => 'Huevos frescos de granja.',
                'idAlmacen' => 2,
                'priceBuy' => 0.30,
                'priceSale' => 0.50,
                'quantityUnitMeasure' => 1,
                'unitMeasure' => 'unidad',
            ],
        ];

        foreach ($inputs as $i) {
            Input::create([
                ...$i,
                'state' => true,
            ]);
        }
    }
}
