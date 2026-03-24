<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends Model
{
    protected $table = 'auditoria';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'tabla_afectada',
        'registro_id',
        'accion',
        'datos_anteriores',
        'datos_nuevos',
        'ip',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos'     => 'array',
        'fecha'            => 'datetime',
    ];

    // ── Helpers estáticos ────────────────────────────────

    /**
     * Registra una entrada de auditoría de forma conveniente.
     *
     * Uso:
     *   Auditoria::registrar('pago', $pago->id, 'anulacion', $anterior, null);
     */
    public static function registrar(
        string $tabla,
        int $registroId,
        string $accion,
        ?array $anterior = null,
        ?array $nuevo = null
    ): void {
        static::create([
            'usuario_id'       => auth()->id(),
            'tabla_afectada'   => $tabla,
            'registro_id'      => $registroId,
            'accion'           => $accion,
            'datos_anteriores' => $anterior,
            'datos_nuevos'     => $nuevo,
            'ip'               => request()->ip(),
        ]);
    }

    // ── Relaciones ───────────────────────────────────────

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
