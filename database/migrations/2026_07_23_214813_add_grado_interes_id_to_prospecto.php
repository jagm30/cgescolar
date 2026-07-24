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
        Schema::table('prospecto', function (Blueprint $table) {
            $table->foreignId('grado_interes_id')
                ->nullable()
                ->after('nivel_interes_id')
                ->constrained('grado')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('prospecto', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Grado::class, 'grado_interes_id');
            $table->dropColumn('grado_interes_id');
        });
    }
};
