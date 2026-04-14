<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoliticaRecargo extends Model
{
    protected $table = 'politica_recargo';

    public $timestamps = false;

    protected $fillable = [
        'plan_id',
        'dia_limite_pago',   // día del mes hasta el cual NO hay recargo
        'tipo_recargo',      // porcentaje | monto_fijo
        'valor',
        'tope_maximo',       // nullable — monto máximo de recargo
        'activo',
        'acumular_mensual',  // si true, el recargo se multiplica por los meses de retraso
    ];

    protected $casts = [
        'valor'            => 'decimal:2',
        'tope_maximo'      => 'decimal:2',
        'activo'           => 'boolean',
        'acumular_mensual' => 'boolean',
        'dia_limite_pago'  => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanPago::class, 'plan_id');
    }

    // ── Helpers ───────────────────────────────────────────

    /**
     * Calcula el monto de recargo para un monto base dado.
     *
     * @param float $montoBase  Saldo pendiente sobre el que se calcula el recargo.
     * @param int   $meses      Meses de retraso acumulados (≥ 1).
     *                          Solo se usa cuando acumular_mensual = true.
     *
     * Respeta el tope_maximo (si está configurado) sobre el total final.
     */
    public function calcular(float $montoBase, int $meses = 1): float
    {
        if ($this->tipo_recargo === 'porcentaje') {
            $recargoPorMes = round($montoBase * ($this->valor / 100), 2);
        } else {
            $recargoPorMes = (float) $this->valor;
        }

        // Acumulación mensual: si lleva N meses de retraso, cobrar N × recargo
        $meses  = max(1, $meses);
        $recargo = $this->acumular_mensual
            ? round($recargoPorMes * $meses, 2)
            : $recargoPorMes;

        // Aplicar tope máximo al recargo total resultante
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
