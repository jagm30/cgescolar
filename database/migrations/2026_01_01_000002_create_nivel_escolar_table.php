<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nivel_escolar', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->comment('maternal | preescolar | primaria | secundaria');
            $table->unsignedTinyInteger('orden')->comment('Define el orden de presentación en listas y reportes');
            $table->boolean('activo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nivel_escolar');
    }
};
