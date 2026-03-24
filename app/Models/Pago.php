<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pago extends Model
{
    protected $table = 'pago';
    public $timestamps = false;

    protected $fillable = [
        'cajero_id',
        'monto_total',
        'fecha_pago',
        'forma_pago',
        'referencia',
        'folio_recibo',
        'estado',
        'motivo',
        'autorizado_por',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'fecha_pago'  => 'date',
        'creado_at'   => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────────

    public function scopeVigente($query)
    {
        return $query->where('estado', 'vigente');
    }

    public function scopeAnulado($query)
    {
        return $query->where('estado', 'anulado');
    }

    public function scopeDelDia($query, string $fecha = null)
    {
        return $query->where('fecha_pago', $fecha ?? now()->toDateString());
    }

    // ── Helpers ──────────────────────────────────────────

    /**
     * Cargos cubiertos por este pago (a través de los detalles).
     */
    public function cargos()
    {
        return $this->hasManyThrough(
            Cargo::class,
            PagoDetalle::class,
            'pago_id',
            'id',
            'id',
            'cargo_id'
        );
    }

    /**
     * Total de descuentos aplicados en todos los detalles.
     */
    public function getTotalDescuentosAttribute(): float
    {
        return $this->detalles->sum(fn($d) =>
            (float) $d->descuento_beca + (float) $d->descuento_otros
        );
    }

    /**
     * Total de recargos cobrados en todos los detalles.
     */
    public function getTotalRecargosAttribute(): float
    {
        return (float) $this->detalles->sum('recargo_aplicado');
    }

    // ── Relaciones ───────────────────────────────────────

    public function cajero(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cajero_id');
    }

    public function autorizadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'autorizado_por');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(PagoDetalle::class, 'pago_id');
    }

    public function cfdis(): HasMany
    {
        return $this->hasMany(Cfdi::class, 'pago_id');
    }
}
