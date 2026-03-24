<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacto_familiar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('familia_id')->nullable()->constrained('familia')->nullOnDelete()
                  ->comment('Agrupa contactos de la misma familia');
            $table->boolean('tiene_acceso_portal')->default(false)
                  ->comment('El admin define si este contacto tendrá usuario en el sistema');
            $table->foreignId('usuario_id')->nullable()->constrained('usuario')->nullOnDelete()
                  ->comment('null hasta que el usuario sea creado. Solo aplica si tiene_acceso_portal = true');
            $table->string('nombre');
            $table->string('ap_paterno')->nullable();
            $table->string('ap_materno')->nullable();
            $table->string('telefono_celular', 20)->nullable();
            $table->string('telefono_trabajo', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('curp', 18)->nullable();
            $table->string('foto_url')->nullable()->comment('Usada en reporte de credenciales');
            $table->timestamp('creado_at')->useCurrent();

            $table->index('familia_id');
            $table->index('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacto_familiar');
    }
};
