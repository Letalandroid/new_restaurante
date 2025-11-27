<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Coca Cola 500ml',
                'idCategory' => 4,
                'details' => 'Bebida gaseosa sabor cola en presentación de 500ml.',
                'idAlmacen' => 1,
                'priceSale' => 4.50,
                'quantityUnitMeasure' => 500,
                'unitMeasure' => 'ml',
            ],
            [
                'name' => 'Inca Kola 1L',
                'idCategory' => 4,
                'details' => 'Inca Kola botella de 1 litro.',
                'idAlmacen' => 1,
                'priceSale' => 7.00,
                'quantityUnitMeasure' => 1,
                'unitMeasure' => 'litros',
            ],
            [
                'name' => 'Cerveza Cusqueña 330ml',
                'idCategory' => 6,
                'details' => 'Cerveza Cusqueña dorada en botella de 330ml.',
                'idAlmacen' => 2,
                'priceSale' => 9.50,
                'quantityUnitMeasure' => 330,
                'unitMeasure' => 'ml',
            ],
            [
                'name' => 'Cerveza Pilsen 310ml',
                'idCategory' => 6,
                'details' => 'Cerveza Pilsen Callao botella 310ml.',
                'idAlmacen' => 2,
                'priceSale' => 7.50,
                'quantityUnitMeasure' => 310,
                'unitMeasure' => 'ml',
            ],
            [
                'name' => 'Vino Tinto Malbec 750ml',
                'idCategory' => 6,
                'details' => 'Vino tinto Malbec botella 750ml.',
                'idAlmacen' => 1,
                'priceSale' => 35.00,
                'quantityUnitMeasure' => 750,
                'unitMeasure' => 'ml',
            ],
            [
                'name' => 'Gatorade Naranja 500ml',
                'idCategory' => 4,
                'details' => 'Bebida hidratante Gatorade sabor naranja 500ml.',
                'idAlmacen' => 1,
                'priceSale' => 4.00,
                'quantityUnitMeasure' => 500,
                'unitMeasure' => 'ml',
            ],
            [
                'name' => 'Red Bull 250ml',
                'idCategory' => 4,
                'details' => 'Bebida energizante Red Bull 250ml.',
                'idAlmacen' => 1,
                'priceSale' => 8.00,
                'quantityUnitMeasure' => 250,
                'unitMeasure' => 'ml',
            ],
            [
                'name' => 'Pringles Original 130g',
                'idCategory' => 8,
                'details' => 'Papas Pringles sabor original 130g.',
                'idAlmacen' => 2,
                'priceSale' => 10.00,
                'quantityUnitMeasure' => 130,
                'unitMeasure' => 'g',
            ],
            [
                'name' => 'Galletas Oreo 2 Pack',
                'idCategory' => 8,
                'details' => 'Paquete doble de galletas Oreo.',
                'idAlmacen' => 1,
                'priceSale' => 2.50,
                'quantityUnitMeasure' => 2,
                'unitMeasure' => 'unidad',
            ],
            [
                'name' => 'Helado D’Onofrio Sandwich',
                'idCategory' => 3,
                'details' => 'Helado tipo sándwich de D’Onofrio.',
                'idAlmacen' => 2,
                'priceSale' => 3.50,
                'quantityUnitMeasure' => 1,
                'unitMeasure' => 'unidad',
            ],
        ];

        foreach ($products as $p) {
            Product::create([
                ...$p,
                'state' => true,
                //'foto' => null,
            ]);
        }
    }
}
