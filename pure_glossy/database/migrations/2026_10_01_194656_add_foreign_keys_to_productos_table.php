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
        Schema::table('productos', function (Blueprint $table) {
            $table->foreign(['id_proveedor'], 'productos_ibfk_1')->references(['id_proveedor'])->on('proveedores')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['id_categoria'], 'producxcategoria')->references(['id_categoria'])->on('categorias')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign('productos_ibfk_1');
            $table->dropForeign('producxcategoria');
        });
    }
};
