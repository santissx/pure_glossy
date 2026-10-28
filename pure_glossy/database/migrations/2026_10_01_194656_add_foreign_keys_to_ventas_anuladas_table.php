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
        Schema::table('ventas_anuladas', function (Blueprint $table) {
            $table->foreign(['id_usuario_anulador'], 'useranul')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_venta'], 'ventas_anuladas_ibfk_1')->references(['id_venta'])->on('ventas')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas_anuladas', function (Blueprint $table) {
            $table->dropForeign('useranul');
            $table->dropForeign('ventas_anuladas_ibfk_1');
        });
    }
};
