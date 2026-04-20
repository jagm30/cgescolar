<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionPlanConcepto extends Model
{
    public $timestamps = false;

    protected $table = 'asignacion_plan_concepto';

    protected $fillable = [
        'asignacion_id',
        'concepto_id',
        'monto',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    // ── Relaciones ───────────────────────────────────────

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionPlan::class, 'asignacion_id');
    }

    public function concepto(): BelongsTo
    {
        return $this->belongsTo(ConceptoCobro::class, 'concepto_id');
    }
}
