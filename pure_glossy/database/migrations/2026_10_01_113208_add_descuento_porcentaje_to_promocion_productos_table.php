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
        Schema::table('promocion_productos', function (Blueprint $table) {
            $table->decimal('descuento_porcentaje', 5, 2)->default(0)->after('cantidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promocion_productos', function (Blueprint $table) {
            $table->dropColumn('descuento_porcentaje');
        });
    }
};