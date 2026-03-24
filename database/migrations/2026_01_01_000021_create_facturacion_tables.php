<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('config_fiscal', function (Blueprint $table) {
            $table->id();
            $table->string('rfc', 13);
            $table->string('razon_social');
            $table->string('regimen_fiscal', 10);
            $table->string('cer_url')->nullable()->comment('Ruta segura al certificado .cer');
            $table->string('key_url')->nullable()->comment('Ruta segura al archivo .key');
            $table->string('serie', 5)->default('A');
            $table->unsignedInteger('folio_actual')->default(1);
        });

        Schema::create('cfdi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained('pago')->restrictOnDelete();
            $table->foreignId('config_fiscal_id')->constrained('config_fiscal')->restrictOnDelete();
            $table->foreignId('razon_social_id')->constrained('razon_social_contacto')->restrictOnDelete()
                  ->comment('RFC seleccionado al momento de facturar');
            $table->string('uso_cfdi', 10)
                  ->comment('Puede diferir del uso_cfdi_default si se cambia al facturar');
            $table->string('uuid_sat', 36)->nullable()->unique()
                  ->comment('UUID de timbre fiscal');
            $table->string('xml_url')->nullable();
            $table->string('pdf_url')->nullable();
            $table->timestamp('fecha_timbrado')->nullable();
            $table->string('estado')->default('vigente')->comment('vigente | cancelado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cfdi');
        Schema::dropIfExists('config_fiscal');
    }
};
