<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_pago_concepto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plan_pago')->cascadeOnDelete();
            $table->foreignId('concepto_id')->constrained('concepto_cobro')->restrictOnDelete();
            $table->decimal('monto', 10, 2);
        });

        Schema::create('politica_descuento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plan_pago')->cascadeOnDelete();
            $table->string('nombre')->comment('Ej: Pronto pago, Pago anual, Hermanos');
            $table->string('tipo_valor')->comment('porcentaje | monto_fijo');
            $table->decimal('valor', 10, 2)
                  ->comment('Si tipo_valor=porcentaje: 0-100. Si tipo_valor=monto_fijo: monto en pesos.');
            $table->unsignedTinyInteger('dia_limite')->nullable()
                  ->comment('Día del mes límite para aplicar el descuento');
            $table->boolean('activo')->default(true);

            $table->index('plan_id');
        });

        Schema::create('politica_recargo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plan_pago')->cascadeOnDelete();
            $table->unsignedTinyInteger('dia_limite_pago')
                  ->comment('Día del mes hasta el cual no hay recargo');
            $table->string('tipo_recargo')->comment('porcentaje | monto_fijo');
            $table->decimal('valor', 10, 2);
            $table->decimal('tope_maximo', 10, 2)->nullable()
                  ->comment('Máximo recargo aplicable. null = sin tope');
            $table->boolean('activo')->default(true);

            $table->index('plan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('politica_recargo');
        Schema::dropIfExists('politica_descuento');
        Schema::dropIfExists('plan_pago_concepto');
    }
};
