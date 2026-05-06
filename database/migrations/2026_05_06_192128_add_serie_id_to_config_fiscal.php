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
        Schema::table('config_fiscal', function (Blueprint $table) {
            $table->unsignedBigInteger('serie_id')->nullable()->after('serie')
                  ->comment('SerieID numérico de factura.com (GET /api/v4/series)');
        });
    }

    public function down(): void
    {
        Schema::table('config_fiscal', function (Blueprint $table) {
            $table->dropColumn('serie_id');
        });
    }
};
