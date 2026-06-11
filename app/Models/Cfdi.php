<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cfdi extends Model
{
    protected $table = 'cfdi';
    public $timestamps = false;

    protected $fillable = [
        'pago_id',
        'config_fiscal_id',
        'razon_social_id',
        'tipo',
        'periodicidad',
        'fecha_desde',
        'fecha_hasta',
        'uso_cfdi',
        'uuid_sat',
        'xml_url',
        'pdf_url',
        'fecha_timbrado',
        'estado',
        'factura_uid',
        'folio',
    ];

    protected function casts(): array
    {
        return [
            'fecha_timbrado' => 'datetime',
            'fecha_desde'    => 'date',
            'fecha_hasta'    => 'date',
        ];
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeVigente($query)
    {
        return $query->where('estado', 'vigente');
    }

    public function scopeCancelado($query)
    {
        return $query->where('estado', 'cancelado');
    }

    public function scopeGlobal($query)
    {
        return $query->where('tipo', 'global');
    }

    public function scopeIndividual($query)
    {
        return $query->where('tipo', 'individual');
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

    /**
     * Pagos agrupados en esta factura global (solo aplica para tipo=global).
     */
    public function pagos(): BelongsToMany
    {
        return $this->belongsToMany(Pago::class, 'cfdi_pago');
    }
}
