<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->id();
            // ciclo_seleccionado_id se agrega en migración posterior
            // para evitar referencia circular con ciclo_escolar
            $table->string('nombre');
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->string('rol')->comment('administrador | caja | recepcion | padre');
            $table->boolean('activo')->default(true);
            $table->timestamp('ultimo_acceso')->nullable();
            $table->timestamp('creado_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
