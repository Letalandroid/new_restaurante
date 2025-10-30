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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')
                  ->constrained('employees')
                  ->onDelete('cascade')
                  ->comment('Employee ID');
            $table->date('work_date')->comment('Date of attendance record');
            $table->time('check_in')->nullable()->comment('Time employee started their shift');
            $table->time('check_out')->nullable()->comment('Time employee ended their shift');
            $table->foreignId('status_id')
                  ->nullable()
                  ->constrained('attendance_statuses')
                  ->nullOnDelete()
                  ->comment('Attendance status');
            $table->text('justification')->nullable()->comment('Reason for absence or delay');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
