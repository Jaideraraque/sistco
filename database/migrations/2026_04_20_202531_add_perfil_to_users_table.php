<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('cargo')->nullable()->after('activo');
            $table->string('telefono')->nullable()->after('cargo');
            $table->string('foto')->nullable()->after('telefono');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['cargo', 'telefono', 'foto']);
        });
    }
};