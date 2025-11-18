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
        Schema::create('general_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nombre del feriado o motivo del día libre');
            $table->date('date')->comment('Fecha exacta del feriado');
            $table->boolean('is_recurring')->default(false)->comment('true: se repite cada año, false: único');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_holidays');
    }
};
