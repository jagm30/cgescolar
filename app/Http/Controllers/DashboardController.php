<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Cargo;
use App\Models\CicloEscolar;
use App\Models\Familia;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Prospecto;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function admin()
    {
        $usuario   = auth()->user();
        $cicloId   = $usuario->ciclo_seleccionado_id
                     ?? CicloEscolar::activo()->value('id');

        // ── Alumnos ─────────────────────────────────────────
        $totalAlumnos   = Alumno::count();
        $alumnosActivos = Alumno::activo()->count();

        // ── Inscritos activos en el ciclo actual, agrupados por nivel ──
        $inscritosPorNivel = Inscripcion::query()
            ->where('inscripcion.activo', true)
            ->where('inscripcion.ciclo_id', $cicloId)
            ->join('grupo', 'inscripcion.grupo_id', '=', 'grupo.id')
            ->join('grado', 'grupo.grado_id', '=', 'grado.id')
            ->join('nivel_escolar', 'grado.nivel_id', '=', 'nivel_escolar.id')
            ->select('nivel_escolar.id', 'nivel_escolar.nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('nivel_escolar.id', 'nivel_escolar.nombre')
            ->orderBy('nivel_escolar.orden')
            ->get();

        $totalInscritos = $inscritosPorNivel->sum('total');

        // ── Familias ─────────────────────────────────────────
        $totalFamilias = Familia::where('activo', true)->count();

        // ── Cobros ───────────────────────────────────────────
        $cobradoHoy = Pago::vigente()
            ->delDia()
            ->sum('monto_total');

        $cobradoMes = Pago::vigente()
            ->whereYear('fecha_pago', now()->year)
            ->whereMonth('fecha_pago', now()->month)
            ->sum('monto_total');

        $cobradoAyer = Pago::vigente()
            ->delDia(now()->subDay()->toDateString())
            ->sum('monto_total');

        // ── Cargos pendientes ─────────────────────────────────
        $cargosPendientes = Cargo::conDeuda()->count();
        $montoPendiente   = Cargo::conDeuda()->sum('monto_original');

        $cargosVencidos = Cargo::conDeuda()
            ->where('fecha_vencimiento', '<', now()->toDateString())
            ->count();

        // ── Últimos pagos ─────────────────────────────────────
        $ultimosPagos = Pago::with(['cajero', 'detalles.cargo.concepto'])
            ->vigente()
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        // ── Prospectos activos ────────────────────────────────
        $prospectosPorEtapa = Prospecto::where('ciclo_id', $cicloId)
            ->whereNotIn('etapa', ['inscrito', 'no_concretado'])
            ->select('etapa', DB::raw('COUNT(*) as total'))
            ->groupBy('etapa')
            ->pluck('total', 'etapa');

        $totalProspectos = $prospectosPorEtapa->sum();

        return view('dashboards.admin', compact(
            'totalAlumnos',
            'alumnosActivos',
            'totalInscritos',
            'inscritosPorNivel',
            'totalFamilias',
            'cobradoHoy',
            'cobradoMes',
            'cobradoAyer',
            'cargosPendientes',
            'montoPendiente',
            'cargosVencidos',
            'ultimosPagos',
            'prospectosPorEtapa',
            'totalProspectos',
        ));
    }
}
