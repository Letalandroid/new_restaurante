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
        Schema::create('payroll_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('payroll_id')->constrained('payrolls')->onDelete('cascade');
    $table->string('concept')->comment('Ejemplo: Horas extras, Bono puntualidad, AFP');
    $table->decimal('amount', 10, 2);
    $table->enum('type', ['ingreso', 'descuento']);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_details');
    }
};
