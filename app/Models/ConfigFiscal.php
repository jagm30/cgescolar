<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConfigFiscal extends Model
{
    protected $table = 'config_fiscal';
    public $timestamps = false;

    protected $fillable = [
        'rfc',
        'razon_social',
        'regimen_fiscal',
        'cer_url',
        'key_url',
        'serie',
        'folio_actual',
    ];

    // ── Helpers ──────────────────────────────────────────

    /**
     * Genera el siguiente folio consecutivo e incrementa el contador.
     * Ej: A00000042
     */
    public function siguienteFolio(): string
    {
        $folio = $this->serie . str_pad($this->folio_actual, 8, '0', STR_PAD_LEFT);
        $this->increment('folio_actual');
        return $folio;
    }

    // ── Relaciones ───────────────────────────────────────

    public function cfdis(): HasMany
    {
        return $this->hasMany(Cfdi::class, 'config_fiscal_id');
    }
}
