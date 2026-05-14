<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscripcion', function (Blueprint $table) {
            // grupo_id pasa a nullable: una inscripción anticipada puede no tener grupo aún
            $table->foreignId('grupo_id')->nullable()->change();

            // tipo distingue inscripción vigente de inscripción anticipada al siguiente ciclo
            $table->string('tipo', 20)->default('regular')->after('activo');
        });
    }

    public function down(): void
    {
        Schema::table('inscripcion', function (Blueprint $table) {
            $table->dropColumn('tipo');
            $table->foreignId('grupo_id')->nullable(false)->change();
        });
    }
};
