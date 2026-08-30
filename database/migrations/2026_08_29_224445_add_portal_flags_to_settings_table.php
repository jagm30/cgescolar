<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('portal_fotos_habilitado')->default(false)->after('logo_ruta');
            $table->boolean('portal_autorizado_recoger_habilitado')->default(false)->after('portal_fotos_habilitado');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['portal_fotos_habilitado', 'portal_autorizado_recoger_habilitado']);
        });
    }
};
