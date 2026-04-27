<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargas_datos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nombre_archivo');
            $table->integer('total_clientes')->default(0);
            $table->string('estado')->default('procesando'); // exitoso, advertencias, fallido
            $table->string('modelos_estado')->default('pendiente'); // actualizados, pendiente, fallido
            $table->text('mensaje')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargas_datos');
    }
};