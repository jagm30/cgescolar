<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cargo extends Model
{
    protected $table = 'cargo';
    public $timestamps = false;

    protected $fillable = [
        'inscripcion_id',
        'concepto_id',
        'asignacion_id',
        'generado_por',
        'monto_original',
        'fecha_vencimiento',
        'estado',
        'periodo',
    ];

    protected $casts = [
        'monto_original'    => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'generado_at'       => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────────

    public function scopePendiente($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeParcial($query)
    {
        return $query->where('estado', 'parcial');
    }

    public function scopePagado($query)
    {
        return $query->where('estado', 'pagado');
    }

    public function scopeConDeuda($query)
    {
        return $query->whereIn('estado', ['pendiente', 'parcial']);
    }

    public function scopePorPeriodo($query, string $periodo)
    {
        return $query->where('periodo', $periodo);
    }

    // ── Accessors ────────────────────────────────────────

    /**
     * Estado real calculado en tiempo real.
     * 'vencido' nunca se guarda en BD.
     */
    public function getEstadoRealAttribute(): string
    {
        if (in_array($this->estado, ['pagado', 'condonado'])) {
            return $this->estado;
        }

        $vencido = now()->isAfter($this->fecha_vencimiento);

        return match ($this->estado) {
            'parcial' => $vencido ? 'parcial_vencido' : 'parcial',
            default   => $vencido ? 'vencido' : 'pendiente',
        };
    }

    /**
     * Suma de abonos vigentes del cargo a través de pago_detalle.
     * Solo cuenta detalles cuyo pago encabezado esté vigente.
     */
    public function getSaldoAbonadoAttribute(): float
    {
        return (float) $this->detallesPagos()
            ->whereHas('pago', fn($q) => $q->where('estado', 'vigente'))
            ->sum('monto_abonado');
    }

    /**
     * Monto pendiente base (sin calcular descuentos en tiempo real).
     */
    public function getSaldoPendienteBaseAttribute(): float
    {
        return round((float) $this->monto_original - $this->saldo_abonado, 2);
    }

    // ── Relaciones ───────────────────────────────────────

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class, 'inscripcion_id');
    }

    public function concepto(): BelongsTo
    {
        return $this->belongsTo(ConceptoCobro::class, 'concepto_id');
    }

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionPlan::class, 'asignacion_id');
    }

    public function generadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'generado_por');
    }

    /** Detalles de pago que cubren este cargo */
    public function detallesPagos(): HasMany
    {
        return $this->hasMany(PagoDetalle::class, 'cargo_id');
    }

    /** Detalles vigentes (cuyo pago encabezado no está anulado) */
    public function detallesPagosVigentes(): HasMany
    {
        return $this->hasMany(PagoDetalle::class, 'cargo_id')
                    ->whereHas('pago', fn($q) => $q->where('estado', 'vigente'));
    }

    /** Pagos (encabezados) que cubren este cargo */
    public function pagos()
    {
        return $this->hasManyThrough(
            Pago::class,
            PagoDetalle::class,
            'cargo_id',
            'id',
            'id',
            'pago_id'
        );
    }

    public function descuentos(): HasMany
    {
        return $this->hasMany(DescuentoCargo::class, 'cargo_id');
    }
}
