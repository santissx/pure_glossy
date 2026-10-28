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
        Schema::create('presupuestos_productos', function (Blueprint $table) {
            $table->integer('id_presupuesto_producto', true);
            $table->integer('id_presupuesto')->index('id_presupuesto');
            $table->integer('id_producto');
            $table->integer('id_promocion')->nullable();
            $table->integer('cantidad');
            $table->decimal('precio', 10);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuestos_productos');
    }
};
