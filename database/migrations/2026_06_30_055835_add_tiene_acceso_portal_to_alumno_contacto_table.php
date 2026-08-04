<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alumno_contacto', function (Blueprint $table) {
            $table->boolean('tiene_acceso_portal')->default(false)->after('es_responsable_pago')
                  ->comment('Permiso por alumno: este contacto puede ver a este alumno en el portal');
        });
    }

    public function down(): void
    {
        Schema::table('alumno_contacto', function (Blueprint $table) {
            $table->dropColumn('tiene_acceso_portal');
        });
    }
};
