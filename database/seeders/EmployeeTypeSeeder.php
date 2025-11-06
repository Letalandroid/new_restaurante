<?php

namespace Database\Seeders;

use App\Models\EmployeeType;
use Illuminate\Database\Seeder;

class EmployeeTypeSeeder extends Seeder
{
    public function run(): void
    {
        EmployeeType::create([
            'name' => 'Jefe de Cocina',
            'payment_type' => 'fijo',
            'base_salary' => 2500.00,
            'hourly_rate' => null,
            'has_punctuality_bonus' => true,
            'punctuality_bonus' => 150.00,
            'state' => true,
        ]);

        EmployeeType::create([
            'name' => 'Cocinero',
            'payment_type' => 'fijo',
            'base_salary' => 1800.00,
            'hourly_rate' => null,
            'has_punctuality_bonus' => true,
            'punctuality_bonus' => 100.00,
            'state' => true,
        ]);

        EmployeeType::create([
            'name' => 'Ayudante de Cocina',
            'payment_type' => 'por_hora',
            'base_salary' => null,
            'hourly_rate' => 10.00,
            'has_punctuality_bonus' => false,
            'punctuality_bonus' => null,
            'state' => true,
        ]);

        EmployeeType::create([
            'name' => 'Repartidor Delivery',
            'payment_type' => 'por_hora',
            'base_salary' => null,
            'hourly_rate' => 12.00,
            'has_punctuality_bonus' => true,
            'punctuality_bonus' => 50.00,
            'state' => true,
        ]);

        // EmployeeType::factory(600)->create();
    }
}
