<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoliticaRecargo extends Model
{
    protected $table = 'politica_recargo';

    protected $fillable = [
        'plan_id',
        'dia_limite_pago',  // día del mes hasta el cual NO hay recargo
        'tipo_recargo',     // porcentaje | monto_fijo
        'valor',
        'tope_maximo',      // nullable — monto máximo de recargo
        'activo',
    ];

    protected $casts = [
        'valor'          => 'decimal:2',
        'tope_maximo'    => 'decimal:2',
        'activo'         => 'boolean',
        'dia_limite_pago'=> 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanPago::class, 'plan_id');
    }

    // ── Helpers ───────────────────────────────────────────

    /**
     * Calcula el monto de recargo para un monto base dado.
     * Respeta el tope_maximo si está configurado.
     */
    public function calcular(float $montoBase): float
    {
        if ($this->tipo_recargo === 'porcentaje') {
            $recargo = round($montoBase * ($this->valor / 100), 2);
        } else {
            $recargo = (float) $this->valor;
        }

        if (! is_null($this->tope_maximo) && $recargo > (float) $this->tope_maximo) {
            $recargo = (float) $this->tope_maximo;
        }

        return $recargo;
    }

    /**
     * Indica si el recargo aplica para el día del mes dado (o hoy).
     * El recargo aplica cuando el día actual es POSTERIOR al día límite.
     */
    public function aplicaHoy(?int $dia = null): bool
    {
        if (! $this->activo) return false;

        $dia ??= now()->day;

        return $dia > $this->dia_limite_pago;
    }
}
