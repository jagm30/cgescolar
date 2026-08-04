<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_pago', function (Blueprint $table) {
            $table->dropForeign(['nivel_id']);
            $table->unsignedBigInteger('nivel_id')->nullable()->change();
            $table->foreign('nivel_id')->references('id')->on('nivel_escolar')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('plan_pago', function (Blueprint $table) {
            $table->dropForeign(['nivel_id']);
            $table->unsignedBigInteger('nivel_id')->nullable(false)->change();
            $table->foreign('nivel_id')->references('id')->on('nivel_escolar')->restrictOnDelete();
        });
    }
};
