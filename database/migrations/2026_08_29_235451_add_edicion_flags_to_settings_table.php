<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('portal_editar_curp_habilitado')->default(false)->after('portal_autorizado_recoger_habilitado');
            $table->boolean('portal_editar_expediente_habilitado')->default(false)->after('portal_editar_curp_habilitado');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['portal_editar_curp_habilitado', 'portal_editar_expediente_habilitado']);
        });
    }
};
