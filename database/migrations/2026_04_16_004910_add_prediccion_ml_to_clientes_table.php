<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->decimal('probabilidad_ml', 5, 2)->nullable()->after('es_moroso');
            $table->string('nivel_riesgo_ml', 10)->nullable()->after('probabilidad_ml');
            $table->timestamp('prediccion_actualizada_at')->nullable()->after('nivel_riesgo_ml');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['probabilidad_ml', 'nivel_riesgo_ml', 'prediccion_actualizada_at']);
        });
    }
};