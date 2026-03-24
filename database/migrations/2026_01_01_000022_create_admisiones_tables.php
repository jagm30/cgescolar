<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospecto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_id')->nullable()->constrained('ciclo_escolar')->nullOnDelete();
            $table->string('nombre');
            $table->date('fecha_nacimiento')->nullable();
            $table->foreignId('nivel_interes_id')->nullable()->constrained('nivel_escolar')->nullOnDelete();
            $table->string('contacto_nombre')->nullable();
            $table->string('contacto_telefono', 20)->nullable();
            $table->string('contacto_email')->nullable();
            $table->string('canal_contacto')->nullable()
                  ->comment('referido | redes | visita_directa | web | otro');
            $table->string('etapa')->default('prospecto')
                  ->comment('prospecto | cita | visita | documentacion | aceptado | inscrito | no_concretado');
            $table->foreignId('responsable_id')->nullable()->constrained('usuario')->nullOnDelete();
            $table->date('fecha_primer_contacto')->nullable();
            $table->text('motivo_no_concrecion')->nullable();
            $table->foreignId('alumno_id')->nullable()->constrained('alumno')->nullOnDelete()
                  ->comment('Se llena cuando el prospecto se convierte en alumno');
            $table->timestamp('creado_at')->useCurrent();
        });

        Schema::create('seguimiento_admision', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospecto_id')->constrained('prospecto')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuario')->restrictOnDelete();
            $table->date('fecha');
            $table->string('tipo_accion')->nullable()
                  ->comment('llamada | visita | email | cambio_etapa | nota');
            $table->text('notas')->nullable();
            $table->timestamp('creado_at')->useCurrent();
        });

        Schema::create('doc_admision', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospecto_id')->constrained('prospecto')->cascadeOnDelete();
            $table->string('tipo_documento');
            $table->string('estado')->default('pendiente')
                  ->comment('pendiente | entregado | no_aplica');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_admision');
        Schema::dropIfExists('seguimiento_admision');
        Schema::dropIfExists('prospecto');
    }
};
