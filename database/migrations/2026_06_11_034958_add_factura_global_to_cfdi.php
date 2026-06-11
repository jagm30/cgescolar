<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Modificar tabla cfdi para soportar facturas globales
        Schema::table('cfdi', function (Blueprint $table) {
            // pago_id pasa a nullable: la factura global no pertenece a un pago individual
            $table->dropForeign(['pago_id']);
            $table->foreignId('pago_id')->nullable()->change();
            $table->foreign('pago_id')
                  ->references('id')
                  ->on('pago')
                  ->nullOnDelete();

            // Tipo de CFDI: individual (un pago) o global (varios pagos sin factura)
            $table->enum('tipo', ['individual', 'global'])
                  ->default('individual')
                  ->after('id')
                  ->comment('individual = por pago; global = público en general por período');

            // Periodicidad SAT: 01=diaria, 02=semanal, 03=decena, 04=mensual
            $table->string('periodicidad', 2)
                  ->nullable()
                  ->after('tipo')
                  ->comment('Código SAT de periodicidad (solo para tipo=global)');

            // Rango de fechas que cubre la factura global
            $table->date('fecha_desde')
                  ->nullable()
                  ->after('periodicidad')
                  ->comment('Inicio del período cubierto (solo para tipo=global)');

            $table->date('fecha_hasta')
                  ->nullable()
                  ->after('fecha_desde')
                  ->comment('Fin del período cubierto (solo para tipo=global)');
        });

        // 2. Tabla pivote cfdi_pago: relaciona una factura global con sus pagos
        Schema::create('cfdi_pago', function (Blueprint $table) {
            $table->foreignId('cfdi_id')
                  ->constrained('cfdi')
                  ->cascadeOnDelete();

            $table->foreignId('pago_id')
                  ->constrained('pago')
                  ->restrictOnDelete();

            $table->primary(['cfdi_id', 'pago_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cfdi_pago');

        Schema::table('cfdi', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'periodicidad', 'fecha_desde', 'fecha_hasta']);

            $table->dropForeign(['pago_id']);
            $table->foreignId('pago_id')->nullable(false)->change();
            $table->foreign('pago_id')
                  ->references('id')
                  ->on('pago')
                  ->restrictOnDelete();
        });
    }
};
