<?php

namespace App\Models;

use Carbon\Carbon;
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
        'monto_original' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'generado_at' => 'datetime',
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
     * Mes y año del período formateado: "Agosto 2026".
     * Devuelve cadena vacía si el cargo no tiene período.
     */
    public function getPeriodoLabelAttribute(): string
    {
        if (! $this->periodo) {
            return '';
        }

        // Se fija el día en 1: createFromFormat('Y-m', ...) hereda el día actual y,
        // si el mes destino tiene menos días que hoy (p.ej. hoy 31 y periodo 09),
        // Carbon desborda al mes siguiente (muestra "Octubre" en vez de "Septiembre").
        $fecha = Carbon::createFromFormat('Y-m-d', substr($this->periodo, 0, 7).'-01')->locale('es');

        return ucfirst($fecha->monthName).' '.$fecha->year;
    }

    /**
     * Etiqueta legible del cargo: "Colegiatura Agosto 2026".
     * Para cargos sin periodo (pago único) devuelve solo el nombre del concepto.
     */
    public function getEtiquetaAttribute(): string
    {
        $nombre = $this->concepto?->nombre ?? 'Servicio educativo';

        if (! $this->periodo) {
            return $nombre;
        }

        $fecha = Carbon::createFromFormat('Y-m-d', substr($this->periodo, 0, 7).'-01')->locale('es');

        return $nombre.' '.ucfirst($fecha->monthName).' '.$fecha->year;
    }

    /**
     * Estado real calculado en tiempo real.
     * 'vencido' nunca se guarda en BD.
     */
    public function getEstadoRealAttribute(): string
    {
        if ($this->estado === 'condonado') {
            return $this->estado;
        }

        if ($this->estado === 'pagado' || $this->monto_cubierto >= (float) $this->monto_original) {
            return 'pagado';
        }

        $vencido = now()->isAfter($this->fecha_vencimiento);

        return match ($this->estado) {
            'parcial' => $vencido ? 'parcial_vencido' : 'parcial',
            default => $vencido ? 'vencido' : 'pendiente',
        };
    }

    /**
     * Suma de abonos vigentes del cargo a través de pago_detalle.
     * Solo cuenta detalles cuyo pago encabezado esté vigente.
     */
    public function getSaldoAbonadoAttribute(): float
    {
        return (float) $this->detallesPagos()
            ->whereHas('pago', fn ($q) => $q->where('estado', 'vigente'))
            ->sum('monto_abonado');
    }

    /**
     * Importe que cubre el cargo original: pagos + descuentos vigentes.
     *
     * monto_abonado ya incluye beca, pronto pago y condonación (se calculan una
     * sola vez al momento del cobro y se suman al efectivo recibido — ver
     * CobrosController::crearDetallePago y PagoController::store). Sumarlos de
     * nuevo aquí duplicaba el crédito y hacía que el estado de cuenta y el
     * portal de padres marcaran cargos como pagados sin estarlo.
     */
    public function getMontoCubiertoAttribute(): float
    {
        return $this->saldo_abonado;
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
            ->whereHas('pago', fn ($q) => $q->where('estado', 'vigente'));
    }

    public function pagosVigentes(): HasMany
    {
        return $this->hasMany(Pago::class, 'cargo_id')->where('estado', 'vigente');
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

    public function condonacionDetalles(): HasMany
    {
        return $this->hasMany(CondonacionDetalle::class, 'cargo_id');
    }
}
