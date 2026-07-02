<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal', function (Blueprint $table) {
            $table->id();
            $table->string('numero_empleado', 20)->unique();
            $table->string('nombre', 100);
            $table->string('ap_paterno', 100);
            $table->string('ap_materno', 100)->nullable();
            $table->string('telefono', 20);
            $table->string('email', 150)->unique();
            $table->string('rfc', 13)->nullable()->comment('RFC con homoclave (13 caracteres)');
            $table->string('tipo', 30)->comment('docente | administrativo | mantenimiento');
            $table->text('domicilio');
            $table->string('foto_url')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal');
    }
};
