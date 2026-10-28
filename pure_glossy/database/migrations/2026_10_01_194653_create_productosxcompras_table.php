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
        Schema::create('productosxcompras', function (Blueprint $table) {
            $table->bigInteger('id_pxc', true);
            $table->bigInteger('id_producto')->index('pxc');
            $table->integer('cantidad_agregada');
            $table->decimal('precio_unitario', 10);
            $table->bigInteger('id_compra')->nullable()->index('compra');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productosxcompras');
    }
};
