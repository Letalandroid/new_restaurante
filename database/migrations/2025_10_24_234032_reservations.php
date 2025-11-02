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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers', 'id');
            $table->integer('number_people')->comment('Cantidad de personas');
            $table->date('date')->comment('Fecha de la reserva (dd-mm-yyyy)');
            $table->time('hour')->comment('Hora de la reserva');
            $table->string('reservation_code', 6)->unique()->comment('Código de reserva de 6 dígitos');
            $table->boolean('state')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};