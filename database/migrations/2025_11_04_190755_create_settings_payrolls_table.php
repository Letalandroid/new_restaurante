<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings_payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->timestamps();
        });

        // Configuraciones iniciales
        DB::table('settings_payrolls')->insert([
            ['key' => 'afp_percentage', 'value' => '12'], // Descuento AFP (%)
            ['key' => 'essalud_percentage', 'value' => '9'], // Aporte ESSALUD (%)
            ['key' => 'payroll_period', 'value' => 'mensual'], // Tipo de periodo de pago
            ['key' => 'general_day_offs', 'value' => 'sunday'], // Días libres generales (ej. "saturday,sunday")
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings_payrolls');
    }
};
