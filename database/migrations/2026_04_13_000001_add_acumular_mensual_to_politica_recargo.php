<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('politica_recargo', function (Blueprint $table) {
            $table->boolean('acumular_mensual')
                  ->default(false)
                  ->after('activo')
                  ->comment('Si es true, el recargo se multiplica por los meses de retraso acumulados');
        });
    }

    public function down(): void
    {
        Schema::table('politica_recargo', function (Blueprint $table) {
            $table->dropColumn('acumular_mensual');
        });
    }
};
