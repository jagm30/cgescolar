<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prospecto extends Model
{
    protected $table = 'prospecto';
    public $timestamps = false;

    protected $fillable = [
        'ciclo_id',
        'nombre',
        'fecha_nacimiento',
        'nivel_interes_id',
        'contacto_nombre',
        'contacto_telefono',
        'contacto_email',
        'canal_contacto',
        'etapa',
        'responsable_id',
        'fecha_primer_contacto',
        'motivo_no_concrecion',
        'alumno_id',
    ];

    protected $casts = [
        'fecha_nacimiento'      => 'date',
        'fecha_primer_contacto' => 'date',
        'creado_at'             => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────────

    public function scopeEnProceso($query)
    {
        return $query->whereNotIn('etapa', ['inscrito', 'no_concretado']);
    }

    public function scopePorEtapa($query, string $etapa)
    {
        return $query->where('etapa', $etapa);
    }

    // ── Helpers ──────────────────────────────────────────

    public function estaInscrito(): bool
    {
        return $this->etapa === 'inscrito' && !is_null($this->alumno_id);
    }

    /**
     * Convierte el prospecto en alumno formal.
     * Actualiza etapa y vincula el alumno creado.
     */
    public function convertirAAlumno(Alumno $alumno): void
    {
        $this->alumno_id = $alumno->id;
        $this->etapa     = 'inscrito';
        $this->save();
    }

    // ── Relaciones ───────────────────────────────────────

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_id');
    }

    public function nivelInteres(): BelongsTo
    {
        return $this->belongsTo(NivelEscolar::class, 'nivel_interes_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'responsable_id');
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function seguimientos(): HasMany
    {
        return $this->hasMany(SeguimientoAdmision::class, 'prospecto_id')
                    ->orderBy('fecha', 'asc');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(DocAdmision::class, 'prospecto_id');
    }

    public function documentosPendientes(): HasMany
    {
        return $this->hasMany(DocAdmision::class, 'prospecto_id')
                    ->where('estado', 'pendiente');
    }
}
