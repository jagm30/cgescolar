<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Cargo;
use App\Models\Cfdi;
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

    public function caja()
    {
        $hoy  = now()->toDateString();
        $mes  = now()->month;
        $anio = now()->year;

        // ── Cobros del día ────────────────────────────────────
        $cobradoHoy   = Pago::vigente()->delDia()->sum('monto_total');
        $pagosHoy     = Pago::vigente()->delDia()->count();
        $cobradoAyer  = Pago::vigente()->delDia(now()->subDay()->toDateString())->sum('monto_total');
        $cobradoMes   = Pago::vigente()->whereYear('fecha_pago', $anio)->whereMonth('fecha_pago', $mes)->sum('monto_total');

        // Desglose por forma de pago del día
        $porFormaPago = Pago::vigente()
            ->delDia()
            ->select('forma_pago', DB::raw('COUNT(*) as cantidad'), DB::raw('SUM(monto_total) as total'))
            ->groupBy('forma_pago')
            ->get()
            ->keyBy('forma_pago');

        // ── Cargos ────────────────────────────────────────────
        $cargosPendientes = Cargo::conDeuda()->count();
        $montoPendiente   = Cargo::conDeuda()->sum('monto_original');

        $cargosVencidos   = Cargo::conDeuda()
            ->where('fecha_vencimiento', '<', $hoy)
            ->count();
        $montoVencido     = Cargo::conDeuda()
            ->where('fecha_vencimiento', '<', $hoy)
            ->sum('monto_original');

        // ── Facturas (CFDIs) ──────────────────────────────────
        $cfdisMes        = Cfdi::vigente()
            ->whereYear('fecha_timbrado', $anio)
            ->whereMonth('fecha_timbrado', $mes)
            ->count();
        $cfdisGlobalesMes = Cfdi::vigente()->global()
            ->whereYear('fecha_timbrado', $anio)
            ->whereMonth('fecha_timbrado', $mes)
            ->count();

        // Pagos del día sin CFDI vigente (pendientes de facturar)
        $pagosSinFacturaHoy = Pago::vigente()
            ->delDia()
            ->whereDoesntHave('cfdis', fn ($q) => $q->where('estado', 'vigente'))
            ->whereDoesntHave('cfdiGlobal', fn ($q) => $q->where('estado', 'vigente'))
            ->count();

        // ── Últimos pagos del día ─────────────────────────────
        $ultimosPagos = Pago::with([
            'cajero',
            'detalles.cargo.concepto',
            'detalles.cargo.inscripcion.alumno',
            'cfdis' => fn ($q) => $q->where('estado', 'vigente'),
        ])
            ->vigente()
            ->delDia()
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        // ── Top deudores (los 5 cargos vencidos de mayor monto) ──
        $topDeudores = Cargo::with(['inscripcion.alumno', 'concepto'])
            ->conDeuda()
            ->where('fecha_vencimiento', '<', $hoy)
            ->orderByDesc('monto_original')
            ->limit(5)
            ->get();

        return view('dashboards.caja', compact(
            'cobradoHoy',
            'pagosHoy',
            'cobradoAyer',
            'cobradoMes',
            'porFormaPago',
            'cargosPendientes',
            'montoPendiente',
            'cargosVencidos',
            'montoVencido',
            'cfdisMes',
            'cfdisGlobalesMes',
            'pagosSinFacturaHoy',
            'ultimosPagos',
            'topDeudores',
        ));
    }
}
