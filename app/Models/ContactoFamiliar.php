<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactoFamiliar extends Model
{
    protected $table = 'contacto_familiar';
    public $timestamps = false;

    protected $fillable = [
        'familia_id',
        'tiene_acceso_portal',
        'usuario_id',
        'nombre',
        'ap_paterno',
        'ap_materno',
        'telefono_celular',
        'telefono_trabajo',
        'email',
        'curp',
        'foto_url',
    ];

    protected $casts = [
        'tiene_acceso_portal' => 'boolean',
        'creado_at'           => 'datetime',
    ];

    // ── Helpers ──────────────────────────────────────────

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->ap_paterno} {$this->ap_materno}");
    }

    public function tieneUsuarioCreado(): bool
    {
        return !is_null($this->usuario_id);
    }

    public function estaPendienteDeUsuario(): bool
    {
        return $this->tiene_acceso_portal && is_null($this->usuario_id);
    }

    /** Alumnos accesibles desde el portal (todos los hijos de la familia) */
    public function alumnosDelPortal()
    {
        if (!$this->familia_id) return collect();

        return Alumno::where('familia_id', $this->familia_id)
                     ->where('estado', 'activo')
                     ->get();
    }

    // ── Relaciones ───────────────────────────────────────

    public function familia(): BelongsTo
    {
        return $this->belongsTo(Familia::class, 'familia_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /** Alumnos vinculados a este contacto (vía alumno_contacto) */
    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(
            Alumno::class,
            'alumno_contacto',
            'contacto_id',
            'alumno_id'
        )->withPivot([
            'parentesco',
            'tipo',
            'orden',
            'autorizado_recoger',
            'es_responsable_pago',
            'activo',
        ]);
    }

    public function razonesSociales(): HasMany
    {
        return $this->hasMany(RazonSocialContacto::class, 'contacto_id');
    }

    public function razonSocialPrincipal()
    {
        return $this->razonesSociales()
                    ->where('es_principal', true)
                    ->where('activo', true)
                    ->first();
    }
    public function alumnoContactos(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(\App\Models\AlumnoContacto::class, 'contacto_id')
                ->where('activo', true);
}
}
