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
        Schema::create('productos', function (Blueprint $table) {
            $table->bigInteger('id_producto', true);
            $table->string('nombre');
            $table->string('codigo', 100)->unique('codigo');
            $table->string('codigo_barra', 100)->unique('codigo_barra');
            $table->bigInteger('id_proveedor')->index('id_proveedor');
            $table->integer('stock')->nullable();
            $table->decimal('precio_lista', 10)->nullable();
            $table->bigInteger('id_categoria')->nullable()->index('producxcategoria');
            $table->decimal('precio_venta', 10)->nullable();
            $table->enum('estado', ['activo', 'desactivado'])->nullable()->default('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
