<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ciclo_seleccionado_id se agrega en migración separada para evitar
     * la referencia circular entre usuario y ciclo_escolar.
     * usuario debe existir antes que contacto_familiar (que lo referencia),
     * y ciclo_escolar debe existir antes que usuario (por este campo).
     * La solución es crear usuario sin este campo y agregarlo aquí,
     * una vez que ambas tablas ya existen.
     */
    public function up(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->foreignId('ciclo_seleccionado_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('ciclo_escolar')
                  ->nullOnDelete()
                  ->comment('Último ciclo trabajado. null = carga el más reciente. Solo roles internos.');
        });
    }

    public function down(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->dropForeign(['ciclo_seleccionado_id']);
            $table->dropColumn('ciclo_seleccionado_id');
        });
    }
};
