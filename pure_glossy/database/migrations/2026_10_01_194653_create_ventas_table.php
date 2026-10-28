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
        Schema::create('ventas', function (Blueprint $table) {
            $table->bigInteger('id_venta', true);
            $table->unsignedBigInteger('id_usuario')->index('usersid');
            $table->dateTime('fecha_venta')->useCurrent();
            $table->decimal('monto_total', 10);
            $table->bigInteger('id_metodo_pago')->index('ventas_ibfk_2');
            $table->decimal('descuento', 10)->nullable();
            $table->bigInteger('id_cliente')->nullable()->index('clientes_corrientes');
            $table->string('cliente_nombre')->nullable();
            $table->string('cliente_telefono', 50)->nullable();
            $table->text('observaciones')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
