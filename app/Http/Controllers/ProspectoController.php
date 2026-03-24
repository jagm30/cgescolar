<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProspectoRequest;
use App\Http\Requests\UpdateProspectoEtapaRequest;
use App\Models\Auditoria;
use App\Models\Prospecto;
use App\Models\SeguimientoAdmision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProspectoController extends Controller
{
    /** GET /prospectos */
    public function index(Request $request): JsonResponse
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? \App\Models\CicloEscolar::activo()->value('id');

        $prospectos = Prospecto::with(['nivelInteres', 'responsable', 'alumno'])
            ->where('ciclo_id', $cicloId)
            ->when($request->filled('etapa'),      fn($q) => $q->where('etapa', $request->etapa))
            ->when($request->filled('buscar'),     fn($q) => $q->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('contacto_nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('contacto_telefono', 'like', "%{$request->buscar}%");
            }))
            ->when($request->filled('en_proceso'), fn($q) => $q->enProceso())
            ->orderByDesc('fecha_primer_contacto')
            ->paginate($request->get('per_page', 20));

        return response()->json($prospectos);
    }

    /** GET /prospectos/{id} */
    public function show(int $id): JsonResponse
    {
        $prospecto = Prospecto::with([
            'nivelInteres',
            'responsable',
            'alumno',
            'seguimientos.usuario',
            'documentos',
        ])->findOrFail($id);

        return response()->json($prospecto);
    }

    /** POST /prospectos */
    public function store(StoreProspectoRequest $request): JsonResponse
    {
        $data = array_merge($request->validated(), [
            'responsable_id' => auth()->id(),
            'etapa'          => 'prospecto',
        ]);

        // Si no se especifica ciclo, usar el activo
        if (empty($data['ciclo_id'])) {
            $data['ciclo_id'] = \App\Models\CicloEscolar::activo()->value('id');
        }

        $prospecto = Prospecto::create($data);

        // Registro de seguimiento inicial
        SeguimientoAdmision::create([
            'prospecto_id' => $prospecto->id,
            'usuario_id'   => auth()->id(),
            'fecha'        => now()->toDateString(),
            'tipo_accion'  => 'nota',
            'notas'        => 'Registro inicial del prospecto.',
        ]);

        return response()->json($prospecto->load(['nivelInteres', 'responsable']), 201);
    }

    /**
     * POST /prospectos/{id}/etapa
     * Cambia la etapa del prospecto y registra seguimiento automático.
     */
    public function cambiarEtapa(UpdateProspectoEtapaRequest $request, int $id): JsonResponse
    {
        $prospecto = Prospecto::findOrFail($id);
        $anterior  = $prospecto->toArray();

        $prospecto->update([
            'etapa'                => $request->etapa,
            'motivo_no_concrecion' => $request->motivo_no_concrecion ?? $prospecto->motivo_no_concrecion,
        ]);

        // Registrar seguimiento automático del cambio de etapa
        SeguimientoAdmision::create([
            'prospecto_id' => $prospecto->id,
            'usuario_id'   => auth()->id(),
            'fecha'        => now()->toDateString(),
            'tipo_accion'  => 'cambio_etapa',
            'notas'        => "Cambio de etapa: {$anterior['etapa']} → {$request->etapa}. {$request->notas}",
        ]);

        Auditoria::registrar('prospecto', $prospecto->id, 'update', $anterior, $prospecto->fresh()->toArray());

        return response()->json($prospecto->fresh()->load(['nivelInteres', 'seguimientos.usuario']));
    }

    /**
     * POST /prospectos/{id}/seguimiento
     * Agrega una nota de seguimiento al prospecto.
     */
    public function agregarSeguimiento(Request $request, int $id): JsonResponse
    {
        $prospecto = Prospecto::findOrFail($id);

        $data = $request->validate([
            'tipo_accion' => ['required', 'in:llamada,visita,email,cambio_etapa,nota'],
            'notas'       => ['required', 'string', 'min:5', 'max:1000'],
            'fecha'       => ['required', 'date'],
        ]);

        $seguimiento = SeguimientoAdmision::create(array_merge($data, [
            'prospecto_id' => $prospecto->id,
            'usuario_id'   => auth()->id(),
        ]));

        return response()->json($seguimiento->load('usuario'), 201);
    }

    /**
     * GET /prospectos/metricas
     * Métricas de conversión por etapa y canal para el ciclo activo.
     */
    public function metricas(Request $request): JsonResponse
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? \App\Models\CicloEscolar::activo()->value('id');

        $porEtapa = Prospecto::where('ciclo_id', $cicloId)
            ->selectRaw('etapa, COUNT(*) as total')
            ->groupBy('etapa')
            ->pluck('total', 'etapa');

        $porCanal = Prospecto::where('ciclo_id', $cicloId)
            ->selectRaw('canal_contacto, COUNT(*) as total')
            ->groupBy('canal_contacto')
            ->pluck('total', 'canal_contacto');

        $totalInscritos     = $porEtapa['inscrito'] ?? 0;
        $totalProspectos    = $porEtapa->sum();
        $tasaConversion     = $totalProspectos > 0
            ? round(($totalInscritos / $totalProspectos) * 100, 1)
            : 0;

        return response()->json([
            'por_etapa'       => $porEtapa,
            'por_canal'       => $porCanal,
            'total_prospectos'=> $totalProspectos,
            'total_inscritos' => $totalInscritos,
            'tasa_conversion' => $tasaConversion . '%',
        ]);
    }
}
