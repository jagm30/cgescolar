<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
    Schema::create('credenciales', function (Blueprint $table) {
        $table->id();
        $table->string('nombre'); // Ej: Credencial Primaria 2026
        $table->enum('orientacion', ['vertical', 'horizontal'])->default('vertical');
        
        // Rutas de las imágenes de fondo
        $table->string('fondo_anverso')->nullable();
        $table->string('fondo_reverso')->nullable();

        // Configuración de los elementos (JSON)
        // Aquí guardaremos: { "nombre": {"x": 10, "y": 20, "size": 12}, "foto": {...} }
        $table->json('config_anverso')->nullable();
        $table->json('config_reverso')->nullable();

        $table->boolean('activo')->default(true);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credenciales');
    }
};
