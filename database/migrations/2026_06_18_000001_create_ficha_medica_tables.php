<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Ficha médica general (una por alumno) ──────────────
        Schema::create('ficha_medica', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')
                  ->unique()
                  ->constrained('alumno')
                  ->cascadeOnDelete();

            // Datos biométricos
            $table->string('tipo_sangre', 5)->nullable()
                  ->comment('A+ | A- | B+ | B- | AB+ | AB- | O+ | O-');
            $table->decimal('peso_kg', 5, 2)->nullable();
            $table->decimal('talla_cm', 5, 2)->nullable();

            // Médico de cabecera
            $table->string('medico_nombre')->nullable();
            $table->string('medico_telefono', 20)->nullable();

            // Hospital en emergencia
            $table->string('hospital_preferente')->nullable();

            // Información complementaria
            $table->text('discapacidad')->nullable()
                  ->comment('Descripción de discapacidad si aplica');
            $table->text('observaciones_generales')->nullable();

            // Control
            $table->foreignId('actualizado_por')
                  ->nullable()
                  ->constrained('usuario')
                  ->nullOnDelete();
            $table->timestamp('actualizado_at')->nullable();
            $table->timestamp('creado_at')->useCurrent();
        });

        // ── 2. Condiciones médicas (padecimientos y alergias) ─────
        Schema::create('condicion_medica', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')
                  ->constrained('alumno')
                  ->cascadeOnDelete();

            $table->string('tipo')
                  ->comment('padecimiento | alergia_alimento | alergia_medicamento | alergia_ambiental | discapacidad | otro');
            $table->string('nombre')
                  ->comment('Ej: Asma, Penicilina, Cacahuate, Polen');
            $table->text('descripcion')->nullable()
                  ->comment('Detalles clínicos adicionales');
            $table->string('nivel_riesgo')
                  ->default('leve')
                  ->comment('leve | moderado | grave | critico');
            $table->boolean('requiere_accion')->default(false)
                  ->comment('Si el personal escolar debe intervenir');
            $table->text('accion_requerida')->nullable()
                  ->comment('Ej: Aplicar EpiPen y llamar al 911 inmediatamente');
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_at')->useCurrent();

            $table->index('alumno_id');
            $table->index(['alumno_id', 'tipo'], 'idx_condicion_alumno_tipo');
            $table->index('nivel_riesgo');
        });

        // ── 3. Medicamentos autorizados en escuela ────────────────
        Schema::create('medicamento_autorizado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')
                  ->constrained('alumno')
                  ->cascadeOnDelete();
            $table->foreignId('autorizado_por_contacto')
                  ->constrained('contacto_familiar')
                  ->restrictOnDelete()
                  ->comment('Padre o tutor que autoriza la administración');

            $table->string('nombre_medicamento')
                  ->comment('Ej: Salbutamol inhalador, Ritalin 10mg');
            $table->string('dosis')
                  ->comment('Ej: 2 inhalaciones, 1 tableta');
            $table->string('frecuencia')
                  ->comment('Ej: En caso de crisis, Diario, Cada 8 horas');
            $table->string('horario')->nullable()
                  ->comment('Ej: 12:00 pm con el lunch');
            $table->boolean('requiere_refrigeracion')->default(false);
            $table->text('instrucciones')->nullable()
                  ->comment('Instrucciones especiales para el personal');
            $table->date('vigencia_fin')->nullable()
                  ->comment('Fecha en que vence la autorización del padre');
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_at')->useCurrent();

            $table->index('alumno_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicamento_autorizado');
        Schema::dropIfExists('condicion_medica');
        Schema::dropIfExists('ficha_medica');
    }
};
