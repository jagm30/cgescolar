<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cargo_id')->constrained('cargo')->restrictOnDelete();
            $table->foreignId('cajero_id')->constrained('usuario')->restrictOnDelete();
            $table->decimal('descuento_beca', 10, 2)->default(0)
                  ->comment('Beca aplicada al momento del abono');
            $table->decimal('descuento_otros', 10, 2)->default(0)
                  ->comment('Descuento por política aplicado');
            $table->decimal('recargo_aplicado', 10, 2)->default(0)
                  ->comment('Recargo por mora cobrado en este abono');
            $table->decimal('monto_abonado', 10, 2)->comment('Lo que pagó en este abono');
            $table->date('fecha_pago');
            $table->string('forma_pago')->comment('efectivo | transferencia | tarjeta | cheque');
            $table->string('referencia')->nullable()->comment('Folio bancario para transferencias');
            $table->string('folio_recibo')->unique()->comment('Folio consecutivo único por cada abono');
            $table->string('folio_grupo')->nullable()
                  ->comment('Agrupa abonos del mismo movimiento. null si es individual');
            $table->string('estado')->default('vigente')->comment('vigente | anulado | parcial');
            $table->text('motivo')->nullable();
            $table->foreignId('autorizado_por')->nullable()->constrained('usuario')->nullOnDelete()
                  ->comment('Requerido para anulaciones');
            $table->timestamp('creado_at')->useCurrent();

            $table->index('folio_grupo');
            $table->index(['cargo_id', 'estado'], 'idx_pago_cargo_estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pago');
    }
};
