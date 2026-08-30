<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_pago_concepto', function (Blueprint $table) {
            $table->boolean('facturable')->default(true)->after('monto');
        });
    }

    public function down(): void
    {
        Schema::table('plan_pago_concepto', function (Blueprint $table) {
            $table->dropColumn('facturable');
        });
    }
};
