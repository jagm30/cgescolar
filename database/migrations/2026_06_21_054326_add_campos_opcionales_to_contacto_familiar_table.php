<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacto_familiar', function (Blueprint $table) {
            $table->string('telefono_2', 20)->nullable()->after('telefono_trabajo');
            $table->date('fecha_nacimiento')->nullable()->after('telefono_2');
            $table->string('lugar_trabajo')->nullable()->after('fecha_nacimiento');
            $table->string('puesto')->nullable()->after('lugar_trabajo');
            $table->string('nivel_estudios')->nullable()->after('puesto');
            $table->string('profesion')->nullable()->after('nivel_estudios');
            $table->boolean('vive')->default(true)->after('profesion');
        });
    }

    public function down(): void
    {
        Schema::table('contacto_familiar', function (Blueprint $table) {
            $table->dropColumn(['telefono_2', 'fecha_nacimiento', 'lugar_trabajo', 'puesto', 'nivel_estudios', 'profesion', 'vive']);
        });
    }
};
