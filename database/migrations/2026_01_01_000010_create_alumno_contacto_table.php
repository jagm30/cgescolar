<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumno_contacto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumno')->cascadeOnDelete();
            $table->foreignId('contacto_id')->constrained('contacto_familiar')->cascadeOnDelete();
            $table->string('parentesco')->comment('padre | madre | abuelo | tio | otro');
            $table->string('tipo')->comment('padre | madre | tutor | tercero_autorizado');
            $table->unsignedTinyInteger('orden')->default(1)
                  ->comment('1=principal, 2=secundario, 3=tercero');
            $table->boolean('autorizado_recoger')->default(false);
            $table->boolean('es_responsable_pago')->default(false);
            $table->boolean('activo')->default(true);

            $table->unique(['alumno_id', 'contacto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumno_contacto');
    }
};
