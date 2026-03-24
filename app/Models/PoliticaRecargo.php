<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoliticaRecargo extends Model
{
    protected $table = 'politica_recargo';
    public $timestamps = false;

    protected $fillable = [
        'plan_id',
        'dia_limite_pago',
        'tipo_recargo',
        'valor',
        'tope_maximo',
        'activo',
    ];

    protected $casts = [
        'valor'       => 'decimal:2',
        'tope_maximo' => 'decimal:2',
        'activo'      => 'boolean',
    ];

    // ── Helpers ──────────────────────────────────────────

    /**
     * Calcula el recargo dado un monto base y la fecha de vencimiento.
     * Retorna 0 si el cargo aún no ha vencido.
     */
    public function calcularRecargo(float $montoBase, Carbon $fechaVencimiento): float
    {
        if (now()->lte($fechaVencimiento)) return 0;

        $recargo = $this->tipo_recargo === 'porcentaje'
            ? $montoBase * ((float) $this->valor / 100)
            : (float) $this->valor;

        if ($this->tope_maximo) {
            $recargo = min($recargo, (float) $this->tope_maximo);
        }

        return round($recargo, 2);
    }

    // ── Relaciones ───────────────────────────────────────

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanPago::class, 'plan_id');
    }
}
