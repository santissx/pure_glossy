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
        Schema::create('proveedores', function (Blueprint $table) {
            $table->bigInteger('id_proveedor', true);
            $table->string('nombre');
            $table->string('telefono', 50);
            $table->string('direccion');
            $table->string('email');
            $table->string('nombre_preventista')->nullable();
            $table->string('num_preventista', 20)->nullable();
            $table->enum('estado', ['activo', 'desactivado'])->nullable()->default('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
