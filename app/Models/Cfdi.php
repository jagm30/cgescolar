<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cfdi extends Model
{
    protected $table = 'cfdi';
    public $timestamps = false;

    protected $fillable = [
        'pago_id',
        'config_fiscal_id',
        'razon_social_id',
        'uso_cfdi',
        'uuid_sat',
        'xml_url',
        'pdf_url',
        'fecha_timbrado',
        'estado',
    ];

    protected $casts = [
        'fecha_timbrado' => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────────

    public function scopeVigente($query)
    {
        return $query->where('estado', 'vigente');
    }

    public function scopeCancelado($query)
    {
        return $query->where('estado', 'cancelado');
    }

    // ── Relaciones ───────────────────────────────────────

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }

    public function configFiscal(): BelongsTo
    {
        return $this->belongsTo(ConfigFiscal::class, 'config_fiscal_id');
    }

    public function razonSocial(): BelongsTo
    {
        return $this->belongsTo(RazonSocialContacto::class, 'razon_social_id');
    }
}
