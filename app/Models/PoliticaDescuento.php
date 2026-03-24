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
        'tipo_valor',
        'valor',
        'dia_limite',
        'activo',
    ];

    protected $casts = [
        'valor'  => 'decimal:2',
        'activo' => 'boolean',
    ];

    // ── Helpers ──────────────────────────────────────────

    /**
     * Calcula el monto de descuento dado un monto base.
     */
    public function calcularDescuento(float $montoBase): float
    {
        return $this->tipo_valor === 'porcentaje'
            ? round($montoBase * ((float) $this->valor / 100), 2)
            : (float) $this->valor;
    }

    /**
     * Verifica si el descuento aplica hoy según el día límite configurado.
     */
    public function aplicaHoy(): bool
    {
        if (is_null($this->dia_limite)) return true;

        return now()->day <= $this->dia_limite;
    }

    // ── Relaciones ───────────────────────────────────────

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanPago::class, 'plan_id');
    }
}
