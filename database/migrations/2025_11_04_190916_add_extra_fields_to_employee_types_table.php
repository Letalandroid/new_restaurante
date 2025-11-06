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
    Schema::table('employee_types', function (Blueprint $table) {
        $table->decimal('shift_hours', 5, 2)->default(8)->comment('Horas estándar por día')->after('hourly_rate');
        $table->decimal('rate_overtime', 3, 2)->default(1.5)->comment('Factor multiplicador de pago por hora extra')->after('shift_hours');
    });
}

public function down(): void
{
    Schema::table('employee_types', function (Blueprint $table) {
        $table->dropColumn(['shift_hours', 'rate_overtime']);
    });
}

};
