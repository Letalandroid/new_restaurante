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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->onDelete('cascade');

            // --- Periodo de pago ---
            $table->date('start_date')->comment('Inicio del periodo de pago');
            $table->date('end_date')->comment('Fin del periodo de pago');

            // --- Datos básicos ---
            $table->decimal('base_salary', 10, 2)->default(0)->comment('Sueldo base mensual o proporcional');
            $table->integer('laborable_days')->default(0)->comment('Días laborales del mes');
            $table->integer('days_present')->default(0);
            $table->integer('days_absent')->default(0);
            $table->integer('days_justified')->default(0);

            // --- Horas ---
            $table->decimal('hours_worked', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('overtime_payment', 10, 2)->default(0);

            // --- Bonos, descuentos, proporciones ---
            $table->decimal('bonuses', 10, 2)->default(0)->comment('Bonos o incentivos');
            $table->decimal('absence_discount', 10, 2)->default(0)->comment('Descuento por inasistencias');
            $table->decimal('proportional_base', 10, 2)->default(0)->comment('Sueldo proporcional a días trabajados');

            // --- Totales ---
            $table->decimal('gross_total', 10, 2)->default(0)->comment('Antes de descuentos legales');
            $table->decimal('afp_discount', 10, 2)->default(0)->comment('Descuento legal AFP/ONP');
            $table->decimal('essalud_contribution', 10, 2)->default(0)->comment('Aporte del empleador a EsSalud (no se descuenta)');
            $table->decimal('net_total', 10, 2)->default(0)->comment('Monto final que recibe el empleado');

            // --- Estado ---
            $table->boolean('paid')->default(false)->comment('Indica si ya fue pagado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
