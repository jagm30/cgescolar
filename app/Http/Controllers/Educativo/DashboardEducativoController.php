<?php

namespace App\Http\Controllers\Educativo;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Dashboard principal del módulo educativo.
 *
 * Punto de entrada para docentes y administradores
 * cuando acceden a la sección de calificaciones.
 */
class DashboardEducativoController extends Controller
{
    /**
     * GET /educativo
     *
     * Muestra el dashboard del módulo educativo.
     * En fases posteriores cargará grupos asignados al docente
     * y el estado de los períodos evaluativos abiertos.
     */
    public function index(): View
    {
        return view('educativo.dashboard');
    }
}
