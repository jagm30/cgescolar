<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Alumno extends Model
{
    protected $table = 'alumno';
    public $timestamps = false;

    protected $fillable = [
        'familia_id',
        'matricula',
        'nombre',
        'ap_paterno',
        'ap_materno',
        'fecha_nacimiento',
        'curp',
        'genero',
        'estado',
        'foto_url',
        'observaciones',
        'fecha_inscripcion',
        'fecha_baja',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_inscripcion' => 'date',
        'fecha_baja'       => 'date',
        'creado_at'        => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────────

    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    // ── Helpers ──────────────────────────────────────────

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->ap_paterno} {$this->ap_materno}");
    }

    public function getEdadAttribute(): int
    {
        return $this->fecha_nacimiento->age;
    }

    /** Hermanos del alumno (misma familia, diferente id) */
    public function hermanos()
    {
        if (!$this->familia_id) return collect();

        return static::where('familia_id', $this->familia_id)
                     ->where('id', '!=', $this->id)
                     ->get();
    }

    /** Inscripción activa en el ciclo actual */
    public function inscripcionActiva()
    {
        return $this->inscripciones()
                    ->where('activo', true)
                    ->latest('id')
                    ->first();
    }

    // ── Relaciones ───────────────────────────────────────

    public function familia(): BelongsTo
    {
        return $this->belongsTo(Familia::class, 'familia_id');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'alumno_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentoAlumno::class, 'alumno_id');
    }

    public function becas(): HasMany
    {
        return $this->hasMany(BecaAlumno::class, 'alumno_id');
    }

    public function asignacionesPlanes(): HasMany
    {
        return $this->hasMany(AsignacionPlan::class, 'alumno_id');
    }

    /** Contactos vinculados al alumno (vía alumno_contacto) */
    public function contactos(): BelongsToMany
    {
        return $this->belongsToMany(
            ContactoFamiliar::class,
            'alumno_contacto',
            'alumno_id',
            'contacto_id'
        )->withPivot([
            'parentesco',
            'tipo',
            'orden',
            'autorizado_recoger',
            'es_responsable_pago',
            'activo',
        ])->orderByPivot('orden');
    }

    /** Solo contactos autorizados para recoger */
    public function contactosAutorizados(): BelongsToMany
    {
        return $this->contactos()->wherePivot('autorizado_recoger', true);
    }

    /** Contacto responsable de pagos */
    public function responsablePago(): BelongsToMany
    {
        return $this->contactos()->wherePivot('es_responsable_pago', true);
    }
}
