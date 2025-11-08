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
        Schema::create('reservation_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('waiting_minutes')->comment('Minutos de espera para las reservaciones');
            $table->boolean('auto_expire')->default(true)->comment('Expirar reservaciones automáticamente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_settings');
    }
};