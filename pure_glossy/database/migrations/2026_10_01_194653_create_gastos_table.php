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
        Schema::create('gastos', function (Blueprint $table) {
            $table->bigInteger('id_gasto', true);
            $table->string('descripcion');
            $table->decimal('monto', 10);
            $table->dateTime('fecha_gasto')->useCurrent();
            $table->enum('categoria', ['administrativo', 'logistico', 'cotidiano', 'deudas'])->nullable()->index('gastos_ibfk_1');
            $table->unsignedBigInteger('id_usuario')->index('gastos_usu');
            $table->string('motivo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
