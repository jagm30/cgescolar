<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoliticaDescuento extends Model
{
    protected $table = 'politica_descuento';

    public $timestamps = false;

    protected $fillable = [
        'plan_id',
        'nombre',
        'tipo_valor',   // porcentaje | monto_fijo
        'valor',
        'dia_limite',   // nullable — día del mes límite para aplicar
        'activo',
    ];

    protected $casts = [
        'valor'     => 'decimal:2',
        'activo'    => 'boolean',
        'dia_limite'=> 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanPago::class, 'plan_id');
    }

    // ── Helpers ───────────────────────────────────────────

    /**
     * Calcula el monto de descuento para un monto base dado.
     */
    public function calcular(float $montoBase): float
    {
        if ($this->tipo_valor === 'porcentaje') {
            return round($montoBase * ($this->valor / 100), 2);
        }

        return min((float) $this->valor, $montoBase);
    }

    /**
     * Indica si el descuento aplica para el día del mes actual.
     */
    public function aplicaHoy(): bool
    {
        if (! $this->activo) return false;
        if (is_null($this->dia_limite)) return true;

        return now()->day <= $this->dia_limite;
    }
}
