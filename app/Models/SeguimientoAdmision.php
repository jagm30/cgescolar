<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeguimientoAdmision extends Model
{
    protected $table = 'seguimiento_admision';
    public $timestamps = false;

    protected $fillable = [
        'prospecto_id',
        'usuario_id',
        'fecha',
        'tipo_accion',
        'notas',
    ];

    protected $casts = [
        'fecha'     => 'date',
        'creado_at' => 'datetime',
    ];

    public function prospecto(): BelongsTo
    {
        return $this->belongsTo(Prospecto::class, 'prospecto_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
