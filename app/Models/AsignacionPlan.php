<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsignacionPlan extends Model
{
    protected $table = 'asignacion_plan';
    public $timestamps = false;

    protected $fillable = [
        'plan_id',
        'alumno_id',
        'grupo_id',
        'nivel_id',
        'origen',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    // ── Relaciones ───────────────────────────────────────

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanPago::class, 'plan_id');
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(NivelEscolar::class, 'nivel_id');
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class, 'asignacion_id');
    }
}
