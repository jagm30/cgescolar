<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nivel_id')->constrained('nivel_escolar')->restrictOnDelete();
            $table->string('nombre')->comment('Ej: 1°, 2°, 3°');
            $table->unsignedTinyInteger('numero');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grado');
    }
};
