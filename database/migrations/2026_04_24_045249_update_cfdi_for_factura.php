<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cfdi', function (Blueprint $table) {
            // Hacer razon_social_id nullable para soportar "Público en General"
            $table->dropForeign(['razon_social_id']);
            $table->unsignedBigInteger('razon_social_id')->nullable()->change();
            $table->foreign('razon_social_id')
                  ->references('id')
                  ->on('razon_social_contacto')
                  ->nullOnDelete();

            // UID interno de factura.com (necesario para cancelar y descargar)
            $table->string('factura_uid')->nullable()->after('estado')
                  ->comment('UID interno de factura.com');

            // Folio de la factura (serie + número, ej: A00000001)
            $table->string('folio', 20)->nullable()->after('factura_uid')
                  ->comment('Folio asignado al emitir: serie + número consecutivo');
        });
    }

    public function down(): void
    {
        Schema::table('cfdi', function (Blueprint $table) {
            $table->dropColumn(['factura_uid', 'folio']);
            $table->dropForeign(['razon_social_id']);
            $table->unsignedBigInteger('razon_social_id')->nullable(false)->change();
            $table->foreign('razon_social_id')
                  ->references('id')
                  ->on('razon_social_contacto')
                  ->restrictOnDelete();
        });
    }
};
