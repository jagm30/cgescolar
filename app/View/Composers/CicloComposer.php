<?php

namespace App\View\Composers;

use App\Models\CicloEscolar;
use Illuminate\View\View;

/**
 * View Composer para inyectar los ciclos escolares disponibles
 * en todas las vistas que usen el layout principal.
 *
 * Se registra una sola vez en ViewServiceProvider y
 * se ejecuta automáticamente en cada request que renderice
 * una vista registrada.
 */
class CicloComposer
{
    public function compose(View $view): void
    {
        // Solo inyectar si hay usuario autenticado
        if (!auth()->check()) {
            return;
        }

        $usuario = auth()->user();

        // Ciclos disponibles para el selector
        $ciclosDisponibles = CicloEscolar::orderByDesc('fecha_inicio')->get();

        // Ciclo actual del usuario:
        // — Si ya eligió uno → ese
        // — Si no → el ciclo activo del sistema
        // — Padres → siempre el activo (no pueden elegir)
        $cicloActual = $usuario->esPadre()
            ? CicloEscolar::activo()->first()
            : (CicloEscolar::find($usuario->ciclo_seleccionado_id)
               ?? CicloEscolar::activo()->first());

        $view->with([
            'ciclosDisponibles' => $ciclosDisponibles,
            'cicloActual'       => $cicloActual,
        ]);
    }
}
