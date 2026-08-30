<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'id',
        'nombre_escuela',
        'logo_ruta',
        'portal_fotos_habilitado',
        'portal_autorizado_recoger_habilitado',
    ];

    protected function casts(): array
    {
        return [
            'portal_fotos_habilitado' => 'boolean',
            'portal_autorizado_recoger_habilitado' => 'boolean',
        ];
    }
}
