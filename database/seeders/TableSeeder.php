<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Table;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        // Probabilidad de que una mesa esté activa (entre 60% y 70%)
        $probabilidadTrue = rand(60, 70) / 100;

        $tables = [
            [
                'name' => 'Mesa 1',
                'tablenum' => 'TBL-101',
                'idArea' => 1,
                'idFloor' => 1,
                'capacity' => 4,
            ],
            [
                'name' => 'Mesa 2',
                'tablenum' => 'TBL-102',
                'idArea' => 1,
                'idFloor' => 1,
                'capacity' => 2,
            ],
            [
                'name' => 'Mesa 3',
                'tablenum' => 'TBL-103',
                'idArea' => 2,
                'idFloor' => 1,
                'capacity' => 6,
            ],
            [
                'name' => 'Mesa 4',
                'tablenum' => 'TBL-104',
                'idArea' => 2,
                'idFloor' => 2,
                'capacity' => 4,
            ],
            [
                'name' => 'Mesa 5',
                'tablenum' => 'TBL-105',
                'idArea' => 3,
                'idFloor' => 2,
                'capacity' => 8,
            ],
            [
                'name' => 'Mesa 6',
                'tablenum' => 'TBL-106',
                'idArea' => 3,
                'idFloor' => 2,
                'capacity' => 4,
            ],
            [
                'name' => 'Mesa 7',
                'tablenum' => 'TBL-107',
                'idArea' => 4,
                'idFloor' => 3,
                'capacity' => 2,
            ],
            [
                'name' => 'Mesa 8',
                'tablenum' => 'TBL-108',
                'idArea' => 5,
                'idFloor' => 3,
                'capacity' => 10,
            ],
            [
                'name' => 'Mesa 9',
                'tablenum' => 'TBL-109',
                'idArea' => 4,
                'idFloor' => 1,
                'capacity' => 6,
            ],
            [
                'name' => 'Mesa 10',
                'tablenum' => 'TBL-110',
                'idArea' => 5,
                'idFloor' => 2,
                'capacity' => 4,
            ],
        ];

        foreach ($tables as $t) {
            Table::create([
                ...$t,
                'state' => rand(0, 100) / 100 <= $probabilidadTrue, // true o false según el % definido
            ]);
        }
    }
}
