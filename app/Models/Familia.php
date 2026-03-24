<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Familia extends Model
{
    protected $table = 'familia';
    public $timestamps = false;

    protected $fillable = [
        'apellido_familia',
        'observaciones',
        'activo',
    ];

    protected $casts = [
        'activo'    => 'boolean',
        'creado_at' => 'datetime',
    ];

    // ── Helpers ──────────────────────────────────────────

    /**
     * Devuelve el número de alumnos activos inscritos en un ciclo dado.
     * Útil para calcular la beca de hermanos.
     */
    public function alumnosActivosEnCiclo(int $cicloId): int
    {
        return $this->alumnos()
            ->where('estado', 'activo')
            ->whereHas('inscripciones', fn($q) => $q->where('ciclo_id', $cicloId)->where('activo', true))
            ->count();
    }

    // ── Relaciones ───────────────────────────────────────

    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class, 'familia_id');
    }

    public function contactos(): HasMany
    {
        return $this->hasMany(ContactoFamiliar::class, 'familia_id');
    }

    /** Contactos con acceso al portal */
    public function contactosConAcceso(): HasMany
    {
        return $this->hasMany(ContactoFamiliar::class, 'familia_id')
                    ->where('tiene_acceso_portal', true);
    }

    /** Contactos pendientes de crear usuario */
    public function contactosPendientesUsuario(): HasMany
    {
        return $this->hasMany(ContactoFamiliar::class, 'familia_id')
                    ->where('tiene_acceso_portal', true)
                    ->whereNull('usuario_id');
    }
}
