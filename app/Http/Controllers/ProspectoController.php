<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocAdmisionRequest;
use App\Http\Requests\StoreProspectoRequest;
use App\Http\Requests\UpdateProspectoEtapaRequest;
use App\Jobs\NotificarEventoAdmisionJob;
use App\Models\Auditoria;
use App\Models\CicloEscolar;
use App\Models\ContactoFamiliar;
use App\Models\DocAdmision;
use App\Models\Familia;
use App\Models\Grado;
use App\Models\NivelEscolar;
use App\Models\Prospecto;
use App\Models\SeguimientoAdmision;
use App\Models\Setting;
use App\Traits\RespondsWithJson;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProspectoController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        $cicloId = $request->get('ciclo_id')
            ?? auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $prospectos = Prospecto::with(['ciclo', 'nivelInteres', 'gradoInteres', 'responsable', 'alumno'])
            ->withCount(['seguimientos as seguimientos_manuales_count' => fn ($q) => Prospecto::filtroSeguimientoManual($q)])
            ->where('ciclo_id', $cicloId)
            ->when($request->filled('etapa'), fn ($q) => $q->where('etapa', $request->etapa))
            ->when($request->filled('en_proceso'), fn ($q) => $q->enProceso())
            ->when($request->filled('buscar'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                    ->orWhere('ap_paterno', 'like', "%{$request->buscar}%")
                    ->orWhere('ap_materno', 'like', "%{$request->buscar}%")
                    ->orWhere('contacto_nombre', 'like', "%{$request->buscar}%")
                    ->orWhere('contacto_telefono', 'like', "%{$request->buscar}%");
            }))
            ->when(true, function ($q) use ($request) {
                $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';

                return match ($request->get('sort', 'fecha')) {
                    'prospecto' => $q->orderBy('ap_paterno', $dir)->orderBy('nombre', $dir),
                    'etapa' => $q->orderBy('etapa', $dir)->orderBy('ap_paterno'),
                    'canal' => $q->orderBy('canal_contacto', $dir)->orderBy('ap_paterno'),
                    'contacto' => $q->orderBy('contacto_nombre', $dir)->orderBy('ap_paterno'),
                    'responsable' => $q->orderByRaw("(
                                        SELECT nombre FROM usuario
                                        WHERE id = prospecto.responsable_id
                                        LIMIT 1
                                    ) {$dir}")->orderBy('ap_paterno'),
                    default => $q->orderBy('fecha_primer_contacto', $dir),
                };
            })
            ->paginate($request->get('per_page', 20));

        if ($request->ajax()) {
            return response()->json($prospectos);
        }

        $ciclos = CicloEscolar::orderByDesc('fecha_inicio')->get();
        $niveles = NivelEscolar::activo()->get();

        return view('prospectos.index', compact('prospectos', 'niveles', 'ciclos', 'cicloId'));
    }

    public function show(int $id)
    {
        $prospecto = Prospecto::with([
            'nivelInteres', 'gradoInteres', 'responsable', 'alumno',
            'seguimientos.usuario', 'documentos',
            'familia.alumnos', 'familia.prospectos',
        ])->findOrFail($id);

        $tiposDocumento = $this->tiposDocumento();

        if (request()->ajax()) {
            return response()->json($prospecto);
        }

        $familias = Familia::orderBy('apellido_familia')->get(['id', 'apellido_familia']);
        $puedeEliminar = ! $prospecto->tieneSeguimientosManuales();

        return view('prospectos.show', compact('prospecto', 'tiposDocumento', 'familias', 'puedeEliminar'));
    }

    /** DELETE /prospectos/{id} — Solo si no tiene seguimientos manuales registrados. */
    public function destroy(int $id)
    {
        $prospecto = Prospecto::findOrFail($id);

        if ($prospecto->tieneSeguimientosManuales()) {
            return $this->respuestaError(
                'No se puede eliminar: el prospecto ya tiene seguimientos registrados.'
            );
        }

        $anterior = $prospecto->toArray();
        $nombreCompleto = $prospecto->nombre_completo;

        $prospecto->delete();

        Auditoria::registrar('prospecto', $id, 'delete', $anterior, null);

        return $this->respuestaExito(
            redirectRoute: 'prospectos.index',
            mensaje: "Prospecto '{$nombreCompleto}' eliminado correctamente.",
        );
    }

    public function create()
    {
        $niveles = NivelEscolar::activo()->get();
        $ciclos = CicloEscolar::orderByDesc('fecha_inicio')->take(2)->get();
        $grados = Grado::orderBy('nivel_id')->orderBy('numero')->get();
        $familias = Familia::orderBy('apellido_familia')->get(['id', 'apellido_familia']);

        return view('prospectos.create', compact('niveles', 'ciclos', 'grados', 'familias'));
    }

    public function edit(int $id)
    {
        $prospecto = Prospecto::with(['nivelInteres', 'gradoInteres'])->findOrFail($id);
        $niveles = NivelEscolar::activo()->get();
        $ciclos = CicloEscolar::orderByDesc('fecha_inicio')->take(2)->get();
        $grados = Grado::orderBy('nivel_id')->orderBy('numero')->get();

        return view('prospectos.edit', compact('prospecto', 'niveles', 'ciclos', 'grados'));
    }

    /** POST /prospectos/{id}/familia */
    public function vincularFamilia(Request $request, int $id)
    {
        $request->validate([
            'accion' => ['required', 'in:vincular,nueva,desvincular'],
            'familia_id' => ['required_if:accion,vincular', 'nullable', 'exists:familia,id'],
            'apellido_familia' => ['required_if:accion,nueva', 'nullable', 'string', 'max:200'],
        ]);

        $prospecto = Prospecto::findOrFail($id);

        if ($request->accion === 'desvincular') {
            $prospecto->update(['familia_id' => null]);

            return $this->respuestaExito(redirectRoute: 'prospectos.show', mensaje: 'Familia desvinculada.', routeParams: ['prospecto' => $prospecto->id]);
        }

        if ($request->accion === 'nueva') {
            $familia = Familia::create(['apellido_familia' => $request->apellido_familia]);
        } else {
            $familia = Familia::findOrFail($request->familia_id);
        }

        $prospecto->update(['familia_id' => $familia->id]);
        Auditoria::registrar('prospecto', $prospecto->id, 'update', ['familia_id' => null], ['familia_id' => $familia->id]);

        return $this->respuestaExito(
            redirectRoute: 'prospectos.show',
            mensaje: "Prospecto vinculado a la familia «{$familia->apellido_familia}».",
            routeParams: ['prospecto' => $prospecto->id],
        );
    }

    public function update(StoreProspectoRequest $request, int $id)
    {
        $prospecto = Prospecto::findOrFail($id);

        $prospecto->update($request->validated());

        return $this->respuestaExito(
            redirectRoute: 'prospectos.show',
            jsonData: ['prospecto' => $prospecto->fresh()],
            mensaje: "Prospecto '{$prospecto->nombre_completo}' actualizado correctamente.",
            routeParams: ['prospecto' => $prospecto->id],
        );
    }

    public function store(StoreProspectoRequest $request)
    {
        $familiaId = $this->resolverFamilia($request);

        $contactoNombreCompleto = implode(' ', array_filter([
            strtoupper(trim($request->contacto_nombre ?? '')),
            strtoupper(trim($request->contacto_ap_paterno ?? '')),
            strtoupper(trim($request->contacto_ap_materno ?? '')),
        ]));

        $data = array_merge(
            collect($request->validated())
                ->except(['opcion_familia', 'apellido_familia', 'contacto_ap_paterno', 'contacto_ap_materno'])
                ->all(),
            [
                'responsable_id' => auth()->id(),
                'etapa' => 'prospecto',
                'ciclo_id' => $request->ciclo_id ?? CicloEscolar::activo()->value('id'),
                'familia_id' => $familiaId,
                'contacto_nombre' => $contactoNombreCompleto,
            ]
        );

        $prospecto = Prospecto::create($data);

        if ($familiaId) {
            $this->crearContactoFamiliarSiEsNuevo($request, $familiaId);
        }

        SeguimientoAdmision::create([
            'prospecto_id' => $prospecto->id,
            'usuario_id' => auth()->id(),
            'fecha' => now()->toDateString(),
            'tipo_accion' => 'nota',
            'notas' => 'Registro inicial del prospecto.',
        ]);

        $prospecto->load(['nivelInteres', 'responsable']);

        NotificarEventoAdmisionJob::dispatch('nuevo_prospecto', [
            'asunto' => "Nuevo prospecto: {$prospecto->nombre_completo}",
            'prospecto_nombre' => $prospecto->nombre_completo,
            'nivel' => $prospecto->nivelInteres?->nombre,
            'canal' => $prospecto->canal_contacto,
            'contacto' => $prospecto->contacto_nombre,
            'telefono' => $prospecto->contacto_telefono,
            'email_contacto' => $prospecto->contacto_email,
            'responsable' => auth()->user()->nombre,
            'fecha' => now()->format('d/m/Y H:i'),
        ]);

        return $this->respuestaExito(
            redirectRoute: 'prospectos.show',
            jsonData: ['prospecto' => $prospecto],
            mensaje: "Prospecto '{$prospecto->nombre_completo}' registrado correctamente.",
            jsonStatus: 201,
            routeParams: ['prospecto' => $prospecto->id]
        );
    }

    public function cambiarEtapa(UpdateProspectoEtapaRequest $request, int $id)
    {
        $prospecto = Prospecto::findOrFail($id);
        $anterior = $prospecto->toArray();

        $prospecto->update([
            'etapa' => $request->etapa,
            'motivo_no_concrecion' => $request->motivo_no_concrecion ?? $prospecto->motivo_no_concrecion,
        ]);

        SeguimientoAdmision::create([
            'prospecto_id' => $prospecto->id,
            'usuario_id' => auth()->id(),
            'fecha' => now()->toDateString(),
            'tipo_accion' => 'cambio_etapa',
            'notas' => "Cambio: {$anterior['etapa']} -> {$request->etapa}. {$request->notas}",
        ]);

        Auditoria::registrar('prospecto', $prospecto->id, 'update', $anterior, $prospecto->fresh()->toArray());

        NotificarEventoAdmisionJob::dispatch('cambio_etapa', [
            'asunto' => "Cambio de etapa: {$prospecto->nombre_completo}",
            'prospecto_nombre' => $prospecto->nombre_completo,
            'etapa_anterior' => $anterior['etapa'],
            'etapa_nueva' => $request->etapa,
            'notas' => $request->notas ?? '',
            'responsable' => auth()->user()->nombre,
            'fecha' => now()->format('d/m/Y H:i'),
        ]);

        return $this->respuestaExito(
            redirectRoute: 'prospectos.show',
            jsonData: ['prospecto' => $prospecto->fresh()->load(['nivelInteres', 'seguimientos.usuario'])],
            mensaje: "Etapa actualizada a '{$request->etapa}' correctamente.",
            routeParams: ['prospecto' => $prospecto->id]
        );
    }

    public function agregarSeguimiento(Request $request, int $id)
    {
        $prospecto = Prospecto::findOrFail($id);

        $data = $request->validate([
            'tipo_accion' => ['required', 'in:llamada,visita,email,cambio_etapa,nota'],
            'notas' => ['required', 'string', 'min:5', 'max:1000'],
            'fecha' => ['required', 'date'],
        ]);

        $seguimiento = SeguimientoAdmision::create(array_merge($data, [
            'prospecto_id' => $prospecto->id,
            'usuario_id' => auth()->id(),
        ]));

        // No notificar cambios de etapa: ya se notificó en cambiarEtapa()
        if ($data['tipo_accion'] !== 'cambio_etapa') {
            NotificarEventoAdmisionJob::dispatch('seguimiento', [
                'asunto' => "Seguimiento en {$prospecto->nombre_completo}: ".ucfirst(str_replace('_', ' ', $data['tipo_accion'])),
                'prospecto_nombre' => $prospecto->nombre_completo,
                'tipo_accion' => $data['tipo_accion'],
                'notas' => $data['notas'],
                'fecha_seguimiento' => $data['fecha'],
                'responsable' => auth()->user()->nombre,
                'fecha' => now()->format('d/m/Y H:i'),
            ]);
        }

        return $this->respuestaExito(
            redirectRoute: 'prospectos.show',
            jsonData: ['seguimiento' => $seguimiento->load('usuario')],
            mensaje: 'Seguimiento registrado correctamente.',
            jsonStatus: 201,
            routeParams: ['prospecto' => $prospecto->id]
        );
    }

    public function agregarDocumento(StoreDocAdmisionRequest $request, int $id)
    {
        $prospecto = Prospecto::findOrFail($id);
        $data = $request->validated();
        $archivo = $request->file('archivo');

        $nombreArchivo = time().'_'.preg_replace('/[^A-Za-z0-9._-]/', '_', $archivo->getClientOriginalName());
        $rutaArchivo = $archivo->storeAs("prospectos/{$prospecto->id}/documentos", $nombreArchivo, 'public');

        $tipoDocumento = $data['tipo_documento'] === 'Otro'
            ? trim($data['otro_documento'])
            : $data['tipo_documento'];

        $documentoExistente = $prospecto->documentos
            ->first(fn ($documento) => $this->normalizarTipoDocumento($documento->tipo_documento) === $this->normalizarTipoDocumento($tipoDocumento));

        $documento = $documentoExistente ?: new DocAdmision([
            'prospecto_id' => $prospecto->id,
            'tipo_documento' => Str::squish($tipoDocumento),
        ]);

        if ($documento->exists && $documento->archivo_url) {
            Storage::disk('public')->delete($documento->archivo_url);
        }

        $documento->fill([
            'estado' => 'entregado',
            'archivo_url' => $rutaArchivo,
            'archivo_nombre' => $archivo->getClientOriginalName(),
        ]);
        $documento->save();

        return $this->respuestaExito(
            redirectRoute: 'prospectos.show',
            jsonData: ['documento' => $documento],
            mensaje: 'Documento cargado correctamente.',
            jsonStatus: 201,
            routeParams: ['prospecto' => $prospecto->id]
        );
    }

    public function descargarDocumento(int $id, int $documentoId)
    {
        $prospecto = Prospecto::findOrFail($id);

        $documento = $prospecto->documentos()
            ->whereKey($documentoId)
            ->firstOrFail();

        abort_unless($documento->archivo_url, 404);

        return Storage::disk('public')->download(
            $documento->archivo_url,
            $documento->archivo_nombre ?: basename($documento->archivo_url)
        );
    }

    public function metricas(Request $request)
    {
        $cicloId = $request->get('ciclo_id')
            ?? auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $datos = $this->compilarMetricas($cicloId);

        if ($request->ajax()) {
            return response()->json($datos);
        }

        $ciclos = CicloEscolar::orderByDesc('fecha_inicio')->get();

        $porSeguimientoUsuario = SeguimientoAdmision::with('usuario')
            ->whereHas('prospecto', fn ($q) => $q->where('ciclo_id', $cicloId))
            ->get()
            ->groupBy(fn ($s) => $s->usuario?->nombre ?? 'Sin usuario')
            ->map(fn ($g) => $g->count())
            ->sortByDesc(fn ($v) => $v);

        return view('prospectos.metricas', compact('datos', 'ciclos', 'cicloId', 'porSeguimientoUsuario'));
    }

    public function exportarPdf(int $id)
    {
        $prospecto = Prospecto::with([
            'nivelInteres', 'gradoInteres', 'responsable', 'ciclo',
            'seguimientos.usuario', 'documentos',
        ])->findOrFail($id);

        $logoBase64 = $this->logoBase64(Setting::find(1));

        $pdf = Pdf::setOptions(['defaultMediaType' => 'print', 'dpi' => 150])
            ->loadView('prospectos.seguimiento_pdf', compact('prospecto', 'logoBase64'))
            ->setPaper('letter', 'portrait');

        $nombre = str_replace(' ', '_', $prospecto->nombre_completo);

        return $pdf->download("Seguimiento_{$nombre}.pdf");
    }

    public function metricasImprimir(Request $request)
    {
        $cicloId = $request->get('ciclo_id')
            ?? auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $ciclo = CicloEscolar::find($cicloId);
        $datos = $this->compilarMetricas($cicloId);

        $prospectos = Prospecto::with(['nivelInteres', 'gradoInteres', 'responsable'])
            ->where('ciclo_id', $cicloId)
            ->orderBy('etapa')
            ->orderBy('ap_paterno')
            ->get();

        $porResponsable = Prospecto::with('responsable')
            ->where('ciclo_id', $cicloId)
            ->get()
            ->groupBy(fn ($p) => $p->responsable?->nombre ?? 'Sin asignar')
            ->map(fn ($g) => $g->count())
            ->sortByDesc(fn ($v) => $v);

        $porSeguimientoUsuario = SeguimientoAdmision::with('usuario')
            ->whereHas('prospecto', fn ($q) => $q->where('ciclo_id', $cicloId))
            ->get()
            ->groupBy(fn ($s) => $s->usuario?->nombre ?? 'Sin usuario')
            ->map(fn ($g) => $g->count())
            ->sortByDesc(fn ($v) => $v);

        return view('prospectos.metricas_imprimir', compact(
            'datos', 'ciclo', 'cicloId', 'prospectos',
            'porResponsable', 'porSeguimientoUsuario'
        ));
    }

    public function metricasPdf(Request $request)
    {
        $cicloId = $request->input('ciclo_id');
        $ciclo = CicloEscolar::find($cicloId);
        $datos = $this->compilarMetricas($cicloId);

        $prospectos = Prospecto::with(['nivelInteres', 'gradoInteres', 'responsable'])
            ->where('ciclo_id', $cicloId)
            ->orderBy('etapa')
            ->orderBy('ap_paterno')
            ->get();

        $porResponsable = Prospecto::with('responsable')
            ->where('ciclo_id', $cicloId)
            ->get()
            ->groupBy(fn ($p) => $p->responsable?->nombre ?? 'Sin asignar')
            ->map(fn ($g) => $g->count())
            ->sortByDesc(fn ($v) => $v);

        $porSeguimientoUsuario = SeguimientoAdmision::with('usuario')
            ->whereHas('prospecto', fn ($q) => $q->where('ciclo_id', $cicloId))
            ->get()
            ->groupBy(fn ($s) => $s->usuario?->nombre ?? 'Sin usuario')
            ->map(fn ($g) => $g->count())
            ->sortByDesc(fn ($v) => $v);

        $chartEtapa = $request->input('chart_etapa', '');
        $chartCanal = $request->input('chart_canal', '');
        $chartResponsable = $request->input('chart_responsable', '');
        $logoBase64 = $this->logoBase64(Setting::find(1));

        $pdf = Pdf::setOptions(['defaultMediaType' => 'print', 'dpi' => 150])
            ->loadView('prospectos.metricas_pdf', compact(
                'datos', 'ciclo', 'cicloId', 'prospectos',
                'porResponsable', 'porSeguimientoUsuario',
                'chartEtapa', 'chartCanal', 'chartResponsable',
                'logoBase64'
            ))
            ->setPaper('letter', 'portrait');

        return $pdf->download("Informe_Admisiones_{$ciclo?->nombre}.pdf");
    }

    private function logoBase64(?Setting $setting = null): string
    {
        $candidatos = array_filter([
            $setting?->logo_ruta
                ? public_path('imgs_escuela/reportes/'.$setting->logo_ruta)
                : null,
            public_path('imgs_escuela/reportes/logo_reportes.png'),
        ]);

        foreach ($candidatos as $path) {
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);

                return 'data:image/'.$type.';base64,'.base64_encode(file_get_contents($path));
            }
        }

        return '';
    }

    /** Compila todas las métricas del ciclo dado. */
    private function compilarMetricas(int $cicloId): array
    {
        $porEtapa = Prospecto::where('ciclo_id', $cicloId)
            ->selectRaw('etapa, COUNT(*) as total')
            ->groupBy('etapa')
            ->pluck('total', 'etapa');

        $porCanal = Prospecto::where('ciclo_id', $cicloId)
            ->selectRaw('canal_contacto, COUNT(*) as total')
            ->groupBy('canal_contacto')
            ->pluck('total', 'canal_contacto');

        $totalInscritos = $porEtapa['inscrito'] ?? 0;
        $totalProspectos = $porEtapa->sum();
        $tasaConversion = $totalProspectos > 0
            ? round(($totalInscritos / $totalProspectos) * 100, 1) : 0;

        return [
            'por_etapa' => $porEtapa,
            'por_canal' => $porCanal,
            'total_prospectos' => $totalProspectos,
            'total_inscritos' => $totalInscritos,
            'tasa_conversion' => $tasaConversion.'%',
        ];
    }

    private function tiposDocumentoBase(): array
    {
        return [
            'Acta de nacimiento',
            'CURP',
            'Certificado de estudios',
            'Boletas ciclo anterior',
            'Comprobante de domicilio',
            'Cartilla de vacunacion',
            'Fotos tamano infantil',
            'INE del tutor',
            'Comprobante de pago',
        ];
    }

    private function tiposDocumento(): array
    {
        $tiposPersonalizados = DocAdmision::query()
            ->whereNotIn('tipo_documento', $this->tiposDocumentoBase())
            ->where('tipo_documento', '<>', 'Otro')
            ->orderBy('tipo_documento')
            ->pluck('tipo_documento')
            ->map(fn ($tipo) => Str::squish($tipo))
            ->unique(fn ($tipo) => $this->normalizarTipoDocumento($tipo))
            ->values()
            ->all();

        return [
            ...$this->tiposDocumentoBase(),
            ...$tiposPersonalizados,
            'Otro',
        ];
    }

    private function normalizarTipoDocumento(string $tipoDocumento): string
    {
        return Str::lower(Str::squish($tipoDocumento));
    }

    /**
     * Crea un ContactoFamiliar para la familia del prospecto si no existe
     * ya un contacto con el mismo número de teléfono en esa familia.
     */
    private function crearContactoFamiliarSiEsNuevo(StoreProspectoRequest $request, int $familiaId): void
    {
        $telefono = $request->contacto_telefono;

        $yaExiste = ContactoFamiliar::where('familia_id', $familiaId)
            ->where('telefono_celular', $telefono)
            ->exists();

        if ($yaExiste) {
            return;
        }

        ContactoFamiliar::create([
            'familia_id' => $familiaId,
            'nombre' => strtoupper(trim($request->contacto_nombre ?? '')),
            'ap_paterno' => strtoupper(trim($request->contacto_ap_paterno ?? '')),
            'ap_materno' => strtoupper(trim($request->contacto_ap_materno ?? '')),
            'telefono_celular' => $telefono,
            'email' => $request->contacto_email,
        ]);
    }

    /** Resuelve o crea la familia según la opción enviada en el formulario. */
    private function resolverFamilia(StoreProspectoRequest $request): ?int
    {
        return match ($request->opcion_familia) {
            'existente' => $request->familia_id ?: null,
            'nueva' => Familia::create([
                'apellido_familia' => strtoupper(Str::squish($request->apellido_familia)),
            ])->id,
            default => null,
        };
    }
}
