<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credencial extends Model
{
    // Forzamos el nombre de la tabla en singular por si las dudas
    protected $table = 'credenciales';

    protected $fillable = [
        'nombre',
        'orientacion',
        'fondo_anverso',
        'fondo_reverso',
        'config_anverso',
        'config_reverso',
        'activo'
    ];

    // Esto es magia pura: convierte el JSON a Array automáticamente
    protected $casts = [
        'config_anverso' => 'array',
        'config_reverso' => 'array',
        'activo' => 'boolean',
    ];
}
