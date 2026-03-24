<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuario')->nullOnDelete()
                  ->comment('null si la acción fue ejecutada por el sistema');
            $table->string('tabla_afectada');
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->string('accion')->comment('insert | update | delete | login | anulacion');
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->timestamp('fecha')->useCurrent();
            $table->string('ip', 45)->nullable();

            $table->index(['tabla_afectada', 'registro_id'], 'idx_auditoria_registro');
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};
