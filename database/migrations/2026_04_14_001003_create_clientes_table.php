<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_cliente')->unique();
            $table->enum('tipo_identificacion', ['Cedula', 'NIT', 'Pasaporte']);
            $table->decimal('mensualidad', 10, 2);
            $table->string('megas');
            $table->string('municipio');
            $table->string('vereda')->nullable();
            $table->string('finca')->nullable();
            $table->date('fecha_instalacion');
            $table->string('metodo_pago')->nullable();
            $table->decimal('tasa_mora_historica', 5, 4)->default(0);
            $table->integer('n_moras_historicas')->default(0);
            $table->integer('n_meses_activos')->default(0);
            $table->decimal('antiguedad_meses', 6, 1)->default(0);
            $table->boolean('es_moroso')->default(false);
            $table->decimal('dia_prom_pago_ult12', 5, 1)->nullable();
            $table->integer('moras_ult_3_meses')->default(0);
            $table->integer('moras_ult_6_meses')->default(0);
            $table->integer('racha_limpia_final')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};