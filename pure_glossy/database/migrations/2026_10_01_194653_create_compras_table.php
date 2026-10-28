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
        Schema::create('compras', function (Blueprint $table) {
            $table->bigInteger('id_compra', true);
            $table->decimal('monto_compra', 10);
            $table->dateTime('fecha')->nullable()->useCurrent();
            $table->bigInteger('id_proveedor')->nullable()->index('prov');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
