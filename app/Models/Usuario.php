<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Authenticatable
{
    protected $table = 'usuario';
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

    // Laravel Auth usa 'password' por defecto — mapeamos password_hash
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

    public function scopePadres($query)
    {
        return $query->where('rol', 'padre');
    }

    // ── Helpers ──────────────────────────────────────────

    public function esAdministrador(): bool
    {
        return $this->rol === 'administrador';
    }

    public function esCajero(): bool
    {
        return $this->rol === 'caja';
    }

    public function esRecepcion(): bool
    {
        return $this->rol === 'recepcion';
    }

    public function esPadre(): bool
    {
        return $this->rol === 'padre';
    }

    public function esInterno(): bool
    {
        return in_array($this->rol, ['administrador', 'caja', 'recepcion']);
    }

    // ── Relaciones ───────────────────────────────────────

    public function cicloSeleccionado(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_seleccionado_id');
    }

    /** Contacto familiar al que pertenece este usuario (rol padre) */
    public function contactoFamiliar(): HasOne
    {
        return $this->hasOne(ContactoFamiliar::class, 'usuario_id');
    }

    /** Familia del usuario padre — a través del contacto familiar */
    public function familia()
    {
        return $this->hasOneThrough(
            Familia::class,
            ContactoFamiliar::class,
            'usuario_id',   // FK en contacto_familiar
            'id',           // PK en familia
            'id',           // PK en usuario
            'familia_id'    // FK en contacto_familiar → familia
        );
    }

    /** Alumnos (hijos) del usuario padre — a través de familia */
    public function alumnos()
    {
        return $this->contactoFamiliar
            ?->familia
            ?->alumnos()
            ?? collect();
    }
}
