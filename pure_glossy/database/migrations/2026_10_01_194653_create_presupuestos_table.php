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
        Schema::create('presupuestos', function (Blueprint $table) {
            $table->integer('id_presupuesto', true);
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->string('titulo')->nullable();
            $table->dateTime('fecha');
            $table->decimal('monto_total', 10)->default(0);
            $table->decimal('descuento', 10)->default(0);
            $table->string('nombre_cliente', 150)->nullable();
            $table->string('telefono_cliente', 50)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('estado', 30)->nullable()->default('pendiente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuestos');
    }
};
