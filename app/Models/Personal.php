<?php

namespace App\Models;

use App\Enums\TipoPersonal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    protected $table = 'personal';

    public $timestamps = false;

    protected $fillable = [
        'numero_empleado',
        'nombre',
        'ap_paterno',
        'ap_materno',
        'telefono',
        'email',
        'rfc',
        'tipo',
        'domicilio',
        'foto_url',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'tipo'   => TipoPersonal::class,
        ];
    }

    /** Nombre completo: Nombre Apellido Paterno [Apellido Materno] */
    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->ap_paterno} " . ($this->ap_materno ?? ''));
    }

    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeBuscar(Builder $query, string $termino): Builder
    {
        return $query->where(function (Builder $q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
                ->orWhere('ap_paterno', 'like', "%{$termino}%")
                ->orWhere('ap_materno', 'like', "%{$termino}%")
                ->orWhere('numero_empleado', 'like', "%{$termino}%")
                ->orWhere('email', 'like', "%{$termino}%");
        });
    }
}
