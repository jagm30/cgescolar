<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospecto', function (Blueprint $table) {
            $table->foreignId('familia_id')
                ->nullable()
                ->after('alumno_id')
                ->constrained('familia')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('prospecto', function (Blueprint $table) {
            $table->dropForeign(['familia_id']);
            $table->dropColumn('familia_id');
        });
    }
};
