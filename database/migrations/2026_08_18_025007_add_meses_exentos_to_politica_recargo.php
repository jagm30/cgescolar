<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('politica_recargo', function (Blueprint $table) {
            $table->json('meses_exentos')
                ->nullable()
                ->after('acumular_mensual')
                ->comment('Array de meses (1–12) en los que NO aplica recargo. Ej: [1, 8] = enero y agosto');
        });
    }

    public function down(): void
    {
        Schema::table('politica_recargo', function (Blueprint $table) {
            $table->dropColumn('meses_exentos');
        });
    }
};
