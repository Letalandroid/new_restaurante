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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('idCategory');
            $table->text('details')->nullable();
            $table->unsignedBigInteger('idAlmacen');
            $table->decimal('priceSale', 10, 2);
            $table->decimal('quantityUnitMeasure', 10, 2);
            $table->string(column: 'unitMeasure');
            $table->boolean('state')->default(true);
            $table->string('foto')->default('sin imagen');
            $table->timestamps();

            // Relaciones
            $table->foreign('idCategory')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('cascade');

            $table->foreign('idAlmacen')
                  ->references('id')
                  ->on('almacens')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
