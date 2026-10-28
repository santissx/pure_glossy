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
        Schema::create('promocion_productos', function (Blueprint $table) {
            $table->bigInteger('id_promocion_producto', true);
            $table->bigInteger('id_promocion')->index('id_promocion');
            $table->bigInteger('id_producto')->index('id_producto');
            $table->integer('cantidad')->default(1);
            $table->decimal('descuento_porcentaje', 5)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promocion_productos');
    }
};
