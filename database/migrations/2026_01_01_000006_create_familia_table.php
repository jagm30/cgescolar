<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('familia', function (Blueprint $table) {
            $table->id();
            $table->string('apellido_familia')->comment('Ej: Familia López García');
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('familia');
    }
};
