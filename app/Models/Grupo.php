<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grupo extends Model
{
    protected $table = 'grupo';

    public $timestamps = false;

    protected $fillable = [
        'ciclo_id',
        'grado_id',
        'nombre',
        'cupo_maximo',
        'docente',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // ── Scopes ──────────────────────────────────────────

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    // ── Relaciones ───────────────────────────────────────

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_id');
    }

    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class, 'grado_id');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'grupo_id');
    }

    public function asignacionesPlanes(): HasMany
    {
        return $this->hasMany(AsignacionPlan::class, 'grupo_id');
    }

    public function getNombreCompletoAttribute()
    {
        $nivel = $this->grado->nivel->nombre ?? '';
        $grado = $this->grado->nombre ?? '';
        $grupo = $this->nombre;

        return "$nivel $grado $grupo";
    }
}
