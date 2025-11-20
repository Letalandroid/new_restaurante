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
        if (Almacen::count() === 0) {
            $this->command->warn('⚠️ No hay almacenes en la base de datos. Ejecuta AlmacenSeeder primero.');
            return;
        }

        if (Supplier::count() === 0) {
            $this->command->warn('⚠️ No hay proveedores en la base de datos. Ejecuta SupplierSeeder primero.');
            return;
        }

        // Limpiar registros existentes si es necesario
        // Input::truncate();

        Input::factory()->count(8)->create(); 
        
        $this->command->info('✅ InputSeeder completado. ' . Input::count() . ' insumos creados.');
    }
}
