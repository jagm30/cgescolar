<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Usuario extends Authenticatable
{
    protected $table = 'usuario';

    /**
     * Deshabilitar timestamps automáticos (created_at / updated_at).
     * La tabla usa solo creado_at gestionado manualmente.
     */
    public $timestamps = false;

    protected $fillable = [
        'ciclo_seleccionado_id',
        'nombre',
        'email',
        'password_hash',
        'rol',
        'activo',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'activo'        => 'boolean',
        'ultimo_acceso' => 'datetime',
        'creado_at'     => 'datetime',
    ];

    // ── Laravel Auth ─────────────────────────────────────
    // La columna se llama password_hash, no password.
    // Laravel busca getAuthPassword() para validar credenciales.
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopeInternos($query)
    {
        return $query->whereIn('rol', ['administrador', 'caja', 'recepcion']);
    }

    // ── Helpers de rol ───────────────────────────────────

    public function esAdministrador(): bool { return $this->rol === 'administrador'; }
    public function esCajero(): bool        { return $this->rol === 'caja'; }
    public function esRecepcion(): bool     { return $this->rol === 'recepcion'; }
    public function esPadre(): bool         { return $this->rol === 'padre'; }

    public function esInterno(): bool
    {
        return in_array($this->rol, ['administrador', 'caja', 'recepcion']);
    }

    // ── Relaciones ───────────────────────────────────────

    public function cicloSeleccionado(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_seleccionado_id');
    }

    /** Contacto familiar vinculado (solo rol padre) */
    public function contactoFamiliar(): HasOne
    {
        return $this->hasOne(ContactoFamiliar::class, 'usuario_id');
    }

    /** Familia del usuario padre */
    public function familia()
    {
        return $this->hasOneThrough(
            Familia::class,
            ContactoFamiliar::class,
            'usuario_id',
            'id',
            'id',
            'familia_id'
        );
    }

    /** Alumnos (hijos) del padre logueado */
    public function alumnos()
    {
        return $this->contactoFamiliar
            ?->familia
            ?->alumnos()
            ?? collect();
    }
    public function rutaDashboard(): string
    {
    return match($this->rol) {
        'administrador' => route('admin.dashboard'),
        'caja'          => route('caja.dashboard'),
        'recepcion'     => route('recepcion.dashboard'),
        default         => url('/'), // Una ruta por defecto por si acaso
        };
    }
    
}
