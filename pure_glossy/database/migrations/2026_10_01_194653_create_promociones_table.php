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
        Schema::create('promociones', function (Blueprint $table) {
            $table->bigInteger('id_promocion', true);
            $table->string('nombre');
            $table->decimal('precio', 10);
            $table->decimal('descuento_porcentaje', 5)->default(0);
            $table->string('tipo_descuento')->default('promocion');
            $table->enum('estado', ['activo', 'inactivo'])->nullable()->default('activo');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promociones');
    }
};
