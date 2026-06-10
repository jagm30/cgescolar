<?php

namespace App\Models;

use App\Enums\TipoInscripcion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inscripcion extends Model
{
    protected $table = 'inscripcion';
    public $timestamps = false;

    protected $fillable = [
        'alumno_id',
        'ciclo_id',
        'grupo_id',
        'fecha',
        'activo',
        'tipo',
    ];

    protected function casts(): array
    {
        return [
            'fecha'  => 'date',
            'activo' => 'boolean',
            'tipo'   => TipoInscripcion::class,
        ];
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeActiva($query)
    {
        return $query->where('activo', true);
    }

    public function scopeRegular($query)
    {
        return $query->where('tipo', TipoInscripcion::Regular);
    }

    public function scopeAnticipada($query)
    {
        return $query->where('tipo', TipoInscripcion::Anticipada);
    }

    // ── Helpers ──────────────────────────────────────────

    public function esAnticipada(): bool
    {
        return $this->tipo === TipoInscripcion::Anticipada;
    }

    // ── Relaciones ───────────────────────────────────────

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_id');
    }

    /** grupo_id es nullable en inscripciones anticipadas sin grupo asignado aún */
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class, 'inscripcion_id');
    }

    /** Cargos pendientes o parciales */
    public function cargosPendientes(): HasMany
    {
        return $this->hasMany(Cargo::class, 'inscripcion_id')
                    ->whereIn('estado', ['pendiente', 'parcial']);
    }
}
