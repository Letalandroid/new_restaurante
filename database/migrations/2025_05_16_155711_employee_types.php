<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('employee_types', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique(); // Ejemplo: 'Mesero', 'Cocinero', 'Cajero'
    
    // Define si el salario es fijo o por hora
    $table->enum('payment_type', ['fijo', 'por_hora'])->default('fijo');
    
    // Salario base mensual (si aplica)
    $table->decimal('base_salary', 10, 2)->nullable();
    
    // Tarifa por hora (si aplica)
    $table->decimal('hourly_rate', 10, 2)->nullable();

    // ¿Tiene derecho a bonificación de puntualidad?
    $table->boolean('has_punctuality_bonus')->default(false);

    // Monto de la bonificación por puntualidad (si aplica)
    $table->decimal('punctuality_bonus', 10, 2)->nullable();

    // Estado (activo/inactivo)
    $table->boolean('state')->default(true)->comment('true: activo, false: inactivo');
    
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_types');
    }
};
