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
        Schema::create('ventas_anuladas', function (Blueprint $table) {
            $table->bigInteger('id_venta_anulada', true);
            $table->bigInteger('id_venta')->index('ventas_anuladas_ibfk_1');
            $table->unsignedBigInteger('id_usuario_anulador')->index('useranul');
            $table->string('descripcion');
            $table->dateTime('fecha_anu')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas_anuladas');
    }
};
