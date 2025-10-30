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
        Schema::create('attendance_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Status name: present, late, absent, justified, day off, etc.');
            $table->string('description')->nullable()->comment('Optional detailed description');
            $table->boolean('active')->default(true)->comment('Indicates if the status is currently active');
            $table->timestamps();
        });
// Estados predeterminados de asistencia del restaurante
DB::table('attendance_statuses')->insert([
    ['name' => 'presente', 'description' => 'El empleado asistió y cumplió su turno', 'active' => true],
    ['name' => 'tarde', 'description' => 'El empleado llegó tarde a su turno', 'active' => true],
    ['name' => 'ausente', 'description' => 'El empleado no asistió al trabajo', 'active' => true],
    ['name' => 'justificado', 'description' => 'La ausencia del empleado fue justificada', 'active' => true],
    ['name' => 'día_libre', 'description' => 'El empleado tenía un día libre programado', 'active' => true],
]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_statuses');
    }
};
