<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('mes');
            $table->integer('anio');
            $table->enum('estado', ['OK', 'MORA', 'SUSPENDIDO', 'INACTIVO', 'OTRO']);
            $table->integer('dia_pago')->nullable();
            $table->string('metodo_pago')->nullable();
            $table->string('referencia')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};