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
        Schema::table('grupo', function (Blueprint $table) {
            $table->enum('grupo', ['A', 'B', 'C', 'D'])->nullable()->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('grupo', function (Blueprint $table) {
            $table->dropColumn('grupo');
        });
    }
};
