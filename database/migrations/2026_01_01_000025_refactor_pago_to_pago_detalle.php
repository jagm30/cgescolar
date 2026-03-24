<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reemplaza la tabla pago original por pago + pago_detalle.
     *
     * IMPORTANTE: Esta migración asume que la tabla pago aún no tiene
     * datos en producción. Si ya tiene datos, crear una migración de
     * transformación de datos antes de ejecutar esta.
     */
    public function up(): void
    {
        // 1. Eliminar tabla cfdi primero (depende de pago)
        Schema::dropIfExists('cfdi');

        // 2. Eliminar tabla pago original
        Schema::dropIfExists('pago');

        // 3. Crear nuevo encabezado de pago
        Schema::create('pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cajero_id')
                  ->constrained('usuario')
                  ->restrictOnDelete();
            $table->decimal('monto_total', 10, 2)
                  ->comment('Suma de monto_final de todos los detalles');
            $table->date('fecha_pago');
            $table->string('forma_pago')
                  ->comment('efectivo | transferencia | tarjeta | cheque');
            $table->string('referencia')->nullable()
                  ->comment('Folio bancario para transferencias');
            $table->string('folio_recibo')->unique()
                  ->comment('Folio consecutivo único por movimiento de caja');
            $table->string('estado')->default('vigente')
                  ->comment('vigente | anulado');
            $table->text('motivo')->nullable()
                  ->comment('Motivo de anulación');
            $table->foreignId('autorizado_por')->nullable()
                  ->constrained('usuario')
                  ->nullOnDelete()
                  ->comment('Requerido para anulaciones');
            $table->timestamp('creado_at')->useCurrent();

            $table->index('estado');
            $table->index('fecha_pago');
        });

        // 4. Crear tabla de detalles del pago
        Schema::create('pago_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')
                  ->constrained('pago')
                  ->cascadeOnDelete()
                  ->comment('Encabezado del movimiento de caja');
            $table->foreignId('cargo_id')
                  ->constrained('cargo')
                  ->restrictOnDelete()
                  ->comment('Cargo que se está cubriendo');
            $table->decimal('descuento_beca', 10, 2)->default(0)
                  ->comment('Beca aplicada al momento del pago');
            $table->decimal('descuento_otros', 10, 2)->default(0)
                  ->comment('Descuento por política aplicado');
            $table->decimal('recargo_aplicado', 10, 2)->default(0)
                  ->comment('Recargo por mora cobrado');
            $table->decimal('monto_abonado', 10, 2)
                  ->comment('Monto que se abona a este cargo en este pago');
            $table->decimal('monto_final', 10, 2)
                  ->comment('monto_abonado - descuentos + recargo');

            $table->index('pago_id');
            $table->index('cargo_id');
            $table->unique(['cargo_id', 'pago_id'], 'uq_pago_detalle_cargo');
        });

        // 5. Recrear tabla cfdi referenciando el nuevo pago
        Schema::create('cfdi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')
                  ->constrained('pago')
                  ->restrictOnDelete()
                  ->comment('El CFDI cubre todos los detalles del pago');
            $table->foreignId('config_fiscal_id')
                  ->constrained('config_fiscal')
                  ->restrictOnDelete();
            $table->foreignId('razon_social_id')
                  ->constrained('razon_social_contacto')
                  ->restrictOnDelete()
                  ->comment('RFC seleccionado al momento de facturar');
            $table->string('uso_cfdi', 10);
            $table->string('uuid_sat', 36)->nullable()->unique()
                  ->comment('UUID de timbre fiscal');
            $table->string('xml_url')->nullable();
            $table->string('pdf_url')->nullable();
            $table->timestamp('fecha_timbrado')->nullable();
            $table->string('estado')->default('vigente')
                  ->comment('vigente | cancelado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cfdi');
        Schema::dropIfExists('pago_detalle');
        Schema::dropIfExists('pago');

        // Restaurar pago original con cargo_id
        Schema::create('pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cargo_id')->constrained('cargo')->restrictOnDelete();
            $table->foreignId('cajero_id')->constrained('usuario')->restrictOnDelete();
            $table->decimal('descuento_beca', 10, 2)->default(0);
            $table->decimal('descuento_otros', 10, 2)->default(0);
            $table->decimal('recargo_aplicado', 10, 2)->default(0);
            $table->decimal('monto_abonado', 10, 2);
            $table->date('fecha_pago');
            $table->string('forma_pago');
            $table->string('referencia')->nullable();
            $table->string('folio_recibo')->unique();
            $table->string('folio_grupo')->nullable();
            $table->string('estado')->default('vigente');
            $table->text('motivo')->nullable();
            $table->foreignId('autorizado_por')->nullable()->constrained('usuario')->nullOnDelete();
            $table->timestamp('creado_at')->useCurrent();
        });

        Schema::create('cfdi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained('pago')->restrictOnDelete();
            $table->foreignId('config_fiscal_id')->constrained('config_fiscal')->restrictOnDelete();
            $table->foreignId('razon_social_id')->constrained('razon_social_contacto')->restrictOnDelete();
            $table->string('uso_cfdi', 10);
            $table->string('uuid_sat', 36)->nullable()->unique();
            $table->string('xml_url')->nullable();
            $table->string('pdf_url')->nullable();
            $table->timestamp('fecha_timbrado')->nullable();
            $table->string('estado')->default('vigente');
        });
    }
};
