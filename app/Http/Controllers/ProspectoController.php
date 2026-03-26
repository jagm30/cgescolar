<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProspectoRequest;
use App\Http\Requests\UpdateProspectoEtapaRequest;
use App\Models\Auditoria;
use App\Models\CicloEscolar;
use App\Models\NivelEscolar;
use App\Models\Prospecto;
use App\Models\SeguimientoAdmision;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;

class ProspectoController extends Controller
{
    use RespondsWithJson;

    /** GET /prospectos */
    public function index(Request $request)
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $prospectos = Prospecto::with(['nivelInteres', 'responsable', 'alumno'])
            ->where('ciclo_id', $cicloId)
            ->when($request->filled('etapa'),      fn($q) => $q->where('etapa', $request->etapa))
            ->when($request->filled('en_proceso'), fn($q) => $q->enProceso())
            ->when($request->filled('buscar'),     fn($q) => $q->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('contacto_nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('contacto_telefono', 'like', "%{$request->buscar}%");
            }))
            ->orderByDesc('fecha_primer_contacto')
            ->paginate($request->get('per_page', 20));

        if ($request->ajax()) {
            return response()->json($prospectos);
        }

        $niveles = NivelEscolar::activo()->get();

        return view('prospectos.index', compact('prospectos', 'niveles'));
    }

    /** GET /prospectos/{id} */
    public function show(int $id)
    {
        $prospecto = Prospecto::with([
            'nivelInteres', 'responsable', 'alumno',
            'seguimientos.usuario', 'documentos',
        ])->findOrFail($id);

        if (request()->ajax()) {
            return response()->json($prospecto);
        }

        return view('prospectos.show', compact('prospecto'));
    }

    /** GET /prospectos/create */
    public function create()
    {
        $niveles = NivelEscolar::activo()->get();
        $ciclos  = CicloEscolar::orderByDesc('fecha_inicio')->take(2)->get();

        return view('prospectos.create', compact('niveles', 'ciclos'));
    }

    /** POST /prospectos */
    public function store(StoreProspectoRequest $request)
    {
        $data = array_merge($request->validated(), [
            'responsable_id' => auth()->id(),
            'etapa'          => 'prospecto',
            'ciclo_id'       => $request->ciclo_id
                ?? CicloEscolar::activo()->value('id'),
        ]);

        $prospecto = Prospecto::create($data);

        SeguimientoAdmision::create([
            'prospecto_id' => $prospecto->id,
            'usuario_id'   => auth()->id(),
            'fecha'        => now()->toDateString(),
            'tipo_accion'  => 'nota',
            'notas'        => 'Registro inicial del prospecto.',
        ]);

        return $this->respuestaExito(
            redirectRoute: 'prospectos.show',
            jsonData: ['prospecto' => $prospecto->load(['nivelInteres', 'responsable'])],
            mensaje: "Prospecto '{$prospecto->nombre}' registrado correctamente.",
            jsonStatus: 201
        );
    }

    /**
     * POST /prospectos/{id}/etapa
     * Cambia la etapa y registra seguimiento automático.
     */
    public function cambiarEtapa(UpdateProspectoEtapaRequest $request, int $id)
    {
        $prospecto = Prospecto::findOrFail($id);
        $anterior  = $prospecto->toArray();

        $prospecto->update([
            'etapa'                => $request->etapa,
            'motivo_no_concrecion' => $request->motivo_no_concrecion ?? $prospecto->motivo_no_concrecion,
        ]);

        SeguimientoAdmision::create([
            'prospecto_id' => $prospecto->id,
            'usuario_id'   => auth()->id(),
            'fecha'        => now()->toDateString(),
            'tipo_accion'  => 'cambio_etapa',
            'notas'        => "Cambio: {$anterior['etapa']} → {$request->etapa}. {$request->notas}",
        ]);

        Auditoria::registrar('prospecto', $prospecto->id, 'update', $anterior, $prospecto->fresh()->toArray());

        return $this->respuestaExito(
            redirectRoute: 'prospectos.show',
            jsonData: ['prospecto' => $prospecto->fresh()->load(['nivelInteres', 'seguimientos.usuario'])],
            mensaje: "Etapa actualizada a '{$request->etapa}' correctamente."
        );
    }

    /** POST /prospectos/{id}/seguimiento */
    public function agregarSeguimiento(Request $request, int $id)
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

        return $this->respuestaExito(
            redirectRoute: 'prospectos.show',
            jsonData: ['seguimiento' => $seguimiento->load('usuario')],
            mensaje: 'Seguimiento registrado correctamente.',
            jsonStatus: 201
        );
    }

    /** GET /prospectos/metricas */
    public function metricas(Request $request)
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $porEtapa = Prospecto::where('ciclo_id', $cicloId)
            ->selectRaw('etapa, COUNT(*) as total')
            ->groupBy('etapa')
            ->pluck('total', 'etapa');

        $porCanal = Prospecto::where('ciclo_id', $cicloId)
            ->selectRaw('canal_contacto, COUNT(*) as total')
            ->groupBy('canal_contacto')
            ->pluck('total', 'canal_contacto');

        $totalInscritos  = $porEtapa['inscrito'] ?? 0;
        $totalProspectos = $porEtapa->sum();
        $tasaConversion  = $totalProspectos > 0
            ? round(($totalInscritos / $totalProspectos) * 100, 1) : 0;

        $datos = [
            'por_etapa'        => $porEtapa,
            'por_canal'        => $porCanal,
            'total_prospectos' => $totalProspectos,
            'total_inscritos'  => $totalInscritos,
            'tasa_conversion'  => $tasaConversion . '%',
        ];

        if ($request->ajax()) {
            return response()->json($datos);
        }

        return view('prospectos.metricas', compact('datos'));
    }
}
