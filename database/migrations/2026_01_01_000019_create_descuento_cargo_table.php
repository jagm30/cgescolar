<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('descuento_cargo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cargo_id')->constrained('cargo')->cascadeOnDelete();
            $table->string('tipo')->comment('porcentaje | monto_fijo');
            $table->decimal('valor', 10, 2);
            $table->decimal('monto_aplicado', 10, 2)
                  ->comment('Monto real descontado, calculado al guardar');
            $table->text('motivo');
            $table->foreignId('autorizado_por')->constrained('usuario')->restrictOnDelete();
            $table->foreignId('creado_por')->constrained('usuario')->restrictOnDelete();
            $table->timestamp('creado_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('descuento_cargo');
    }
};
