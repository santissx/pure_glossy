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
        Schema::create('clientes_corrientes', function (Blueprint $table) {
            $table->bigInteger('id_cliente', true);
            $table->string('nombre_y_apellido');
            $table->bigInteger('DNI');
            $table->bigInteger('telefono')->nullable();
            $table->enum('estado', ['activo', 'desactivado'])->nullable()->default('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes_corrientes');
    }
};
