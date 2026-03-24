<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CicloEscolar extends Model
{
    protected $table = 'ciclo_escolar';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
        'creado_at'    => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────────

    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeCerrado($query)
    {
        return $query->where('estado', 'cerrado');
    }

    // ── Relaciones ───────────────────────────────────────

    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class, 'ciclo_id');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'ciclo_id');
    }

    public function planesPago(): HasMany
    {
        return $this->hasMany(PlanPago::class, 'ciclo_id');
    }

    public function becasAlumno(): HasMany
    {
        return $this->hasMany(BecaAlumno::class, 'ciclo_id');
    }

    public function prospectos(): HasMany
    {
        return $this->hasMany(Prospecto::class, 'ciclo_id');
    }

    public function usuariosSeleccionaron(): HasMany
    {
        return $this->hasMany(Usuario::class, 'ciclo_seleccionado_id');
    }
}
