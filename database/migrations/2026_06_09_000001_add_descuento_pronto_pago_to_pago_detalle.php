<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pago_detalle', function (Blueprint $table) {
            $table->decimal('descuento_pronto_pago', 10, 2)->default(0)
                ->after('descuento_beca')
                ->comment('Descuento por pronto pago aplicado automáticamente');
        });
    }

    public function down(): void
    {
        Schema::table('pago_detalle', function (Blueprint $table) {
            $table->dropColumn('descuento_pronto_pago');
        });
    }
};
