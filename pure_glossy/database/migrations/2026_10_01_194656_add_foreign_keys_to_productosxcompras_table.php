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
        Schema::table('productosxcompras', function (Blueprint $table) {
            $table->foreign(['id_compra'], 'compra')->references(['id_compra'])->on('compras')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_producto'], 'pxc')->references(['id_producto'])->on('productos')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productosxcompras', function (Blueprint $table) {
            $table->dropForeign('compra');
            $table->dropForeign('pxc');
        });
    }
};
