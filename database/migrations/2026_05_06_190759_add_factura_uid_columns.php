<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('razon_social_contacto', function (Blueprint $table) {
            $table->string('factura_uid', 50)->nullable()->after('activo')
                  ->comment('UID del cliente en factura.com (se asigna al primer CFDI)');
        });

        Schema::table('config_fiscal', function (Blueprint $table) {
            $table->string('publico_general_uid', 50)->nullable()
                  ->comment('UID de XAXX010101000 en factura.com');
        });
    }

    public function down(): void
    {
        Schema::table('razon_social_contacto', function (Blueprint $table) {
            $table->dropColumn('factura_uid');
        });

        Schema::table('config_fiscal', function (Blueprint $table) {
            $table->dropColumn('publico_general_uid');
        });
    }
};
