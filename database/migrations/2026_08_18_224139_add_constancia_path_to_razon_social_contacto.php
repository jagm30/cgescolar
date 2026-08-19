<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('razon_social_contacto', function (Blueprint $table) {
            $table->string('constancia_path', 500)->nullable()
                  ->after('activo')
                  ->comment('Ruta del archivo PDF/imagen de la constancia de situación fiscal del SAT');
        });
    }

    public function down(): void
    {
        Schema::table('razon_social_contacto', function (Blueprint $table) {
            $table->dropColumn('constancia_path');
        });
    }
};
