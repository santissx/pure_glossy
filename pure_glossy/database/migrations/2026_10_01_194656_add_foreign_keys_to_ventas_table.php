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
        Schema::table('ventas', function (Blueprint $table) {
            $table->foreign(['id_cliente'], 'clientes_Corrientes')->references(['id_cliente'])->on('clientes_corrientes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_usuario'], 'usersid')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_metodo_pago'], 'ventas_ibfk_2')->references(['id_metodo_pago'])->on('metodos_pago')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign('clientes_Corrientes');
            $table->dropForeign('usersid');
            $table->dropForeign('ventas_ibfk_2');
        });
    }
};
