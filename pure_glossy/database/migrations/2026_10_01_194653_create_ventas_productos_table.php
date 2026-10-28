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
        Schema::create('ventas_productos', function (Blueprint $table) {
            $table->bigInteger('id_venta_producto', true);
            $table->bigInteger('id_venta')->index('id_venta');
            $table->bigInteger('id_producto')->index('id_producto');
            $table->integer('cantidad');
            $table->decimal('precio', 10)->nullable();
            $table->decimal('precio_lista', 10)->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas_productos');
    }
};
