<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Cargo;
use App\Models\Cfdi;
use App\Models\ContactoFamiliar;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\RazonSocialContacto;
use App\Services\CfdiService;
use App\Services\FacturaComService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class PortalPadreController extends Controller
{
    public function dashboard(): View
    {
        $alumnos = $this->alumnosDelPadre();
        $resumen = $this->resumenFamilia($alumnos);

        return view('portal.dashboard', compact('alumnos', 'resumen'));
    }

    public function hijos(): View|JsonResponse
    {
        $alumnos = $this->alumnosDelPadre();

        if (request()->ajax()) {
            return response()->json($alumnos);
        }

        return view('portal.hijos', compact('alumnos'));
    }

    public function estadoCuenta(int $alumnoId): View|JsonResponse|RedirectResponse
    {
        $this->verificarAccesoAlumno($alumnoId);

        $inscripcion = Inscripcion::query()
            ->with(['alumno', 'grupo.grado.nivel', 'ciclo'])
            ->where('alumno_id', $alumnoId)
            ->where('activo', true)
            ->latest('id')
            ->first();

        if (! $inscripcion) {
            if (request()->ajax()) {
                return response()->json(['message' => 'Sin inscripcion activa.'], 404);
            }

            return back()->with('error', 'No tiene inscripcion activa.');
        }

        $cargos = Cargo::with(['concepto', 'detallesPagosVigentes'])
            ->where('inscripcion_id', $inscripcion->id)
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(fn (Cargo $cargo) => [
                'id' => $cargo->id,
                'concepto' => $cargo->concepto->nombre,
                'periodo' => $cargo->periodo,
                'monto_original' => $cargo->monto_original,
                'saldo_abonado' => $cargo->saldo_abonado,
                'saldo_pendiente' => max(0, $cargo->saldo_pendiente_base),
                'estado' => $cargo->detallesPagosVigentes->isNotEmpty() || $cargo->estado === 'condonado'
                    ? $cargo->estado_real
                    : 'pendiente',
                'fecha_vencimiento' => $cargo->fecha_vencimiento,
                'puede_facturar' => $cargo->detallesPagosVigentes->isNotEmpty(),
            ]);

        $resumen = [
            'total_cargado' => $cargos->sum('monto_original'),
            'total_pendiente' => $cargos->sum('saldo_pendiente'),
            'total_pagado' => $cargos->sum('saldo_abonado'),
            'total_cargos' => $cargos->count(),
            'cargos_vencidos' => $cargos->filter(fn (array $cargo) => str_contains($cargo['estado'], 'vencido'))->count(),
        ];

        $alumno = $inscripcion->alumno;

        if (request()->ajax()) {
            return response()->json(['resumen' => $resumen, 'cargos' => $cargos]);
        }

        return view('portal.estado-cuenta', compact('alumno', 'cargos', 'inscripcion', 'resumen'));
    }

    public function historialPagos(int $alumnoId): View|JsonResponse
    {
        $this->verificarAccesoAlumno($alumnoId);

        $pagos = Pago::with(['detalles.cargo.concepto', 'cfdis'])
            ->whereHas('detalles.cargo.inscripcion', fn ($query) => $query->where('alumno_id', $alumnoId))
            ->where('estado', 'vigente')
            ->orderByDesc('fecha_pago')
            ->get()
            ->map(fn (Pago $pago) => [
                'id' => $pago->id,
                'folio_recibo' => $pago->folio_recibo,
                'conceptos' => $pago->detalles->map(fn ($detalle) => $detalle->cargo->etiqueta)->join(', '),
                'monto_total' => $pago->monto_total,
                'fecha_pago' => $pago->fecha_pago,
                'forma_pago' => $pago->forma_pago,
                'tiene_factura'   => $pago->cfdis->where('estado', 'vigente')->isNotEmpty(),
                'cfdi_id'         => $pago->cfdis->where('estado', 'vigente')->first()?->id,
                'cfdi_uuid'       => $pago->cfdis->where('estado', 'vigente')->first()?->uuid_sat,
                'puede_facturar'  => $this->pagoPuedeFacturarse($pago),
            ]);

        $alumno = Alumno::findOrFail($alumnoId);

        if (request()->ajax()) {
            return response()->json($pagos);
        }

        $contacto         = auth()->user()->contactoFamiliar()->with('familia')->first();
        $razonesSociales  = $contacto?->familia_id
            ? RazonSocialContacto::whereIn('contacto_id',
                    ContactoFamiliar::where('familia_id', $contacto->familia_id)->pluck('id')
                )
                ->where('activo', true)
                ->orderByDesc('es_principal')
                ->get(['id', 'rfc', 'razon_social', 'uso_cfdi_default', 'es_principal'])
            : collect();

        return view('portal.historial-pagos', compact('alumno', 'pagos', 'razonesSociales'));
    }

    public function razonesSociales(): View|JsonResponse
    {
        $contacto = auth()->user()->contactoFamiliar()->with('familia')->first();

        // Todas las razones sociales activas de todos los contactos de la familia
        $razonesSociales = $contacto?->familia_id
            ? RazonSocialContacto::with('contacto')
                ->whereIn('contacto_id',
                    \App\Models\ContactoFamiliar::where('familia_id', $contacto->familia_id)->pluck('id')
                )
                ->where('activo', true)
                ->orderByDesc('es_principal')
                ->orderBy('contacto_id')
                ->get()
            : collect();

        if (request()->ajax()) {
            return response()->json($razonesSociales);
        }

        return view('portal.razones-sociales', [
            'razonesSociales' => $razonesSociales,
            'miContactoId'    => $contacto?->id,
        ]);
    }

    /** POST /portal/razones-sociales */
    public function storeRazonSocial(Request $request): JsonResponse
    {
        $contacto = auth()->user()->contactoFamiliar;

        if (! $contacto) {
            return response()->json(['status' => 'error', 'mensaje' => 'Sin contacto familiar asociado.'], 403);
        }

        $data = $request->validate([
            'rfc'              => ['required', 'string', 'between:12,13', 'regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/'],
            'razon_social'     => ['required', 'string', 'max:300'],
            'regimen_fiscal'   => ['required', 'string', 'max:10'],
            'domicilio_fiscal' => ['required', 'string', 'size:5', 'regex:/^[0-9]{5}$/'],
            'uso_cfdi_default' => ['required', 'string', 'max:10'],
            'es_principal'     => ['boolean'],
        ], [
            'rfc.regex'                 => 'El formato del RFC no es válido.',
            'razon_social.required'     => 'La razón social es obligatoria.',
            'regimen_fiscal.required'   => 'El régimen fiscal es obligatorio.',
            'domicilio_fiscal.size'     => 'El código postal debe tener exactamente 5 dígitos.',
            'domicilio_fiscal.regex'    => 'El código postal debe contener solo números.',
            'uso_cfdi_default.required' => 'El uso de CFDI es obligatorio.',
        ]);

        $total = RazonSocialContacto::where('contacto_id', $contacto->id)->where('activo', true)->count();
        if ($total >= 3) {
            return response()->json(['status' => 'error', 'mensaje' => 'Ya tienes 3 razones sociales registradas, que es el máximo permitido.'], 422);
        }

        $rfc = strtoupper($data['rfc']);
        if (RazonSocialContacto::where('contacto_id', $contacto->id)->where('rfc', $rfc)->exists()) {
            return response()->json(['status' => 'error', 'mensaje' => 'Este RFC ya está registrado en tu cuenta.'], 422);
        }

        $esPrincipal = $request->boolean('es_principal', false);
        if ($esPrincipal) {
            RazonSocialContacto::where('contacto_id', $contacto->id)->update(['es_principal' => false]);
        }

        $rs = RazonSocialContacto::create([
            'contacto_id'     => $contacto->id,
            'rfc'             => $rfc,
            'razon_social'    => $data['razon_social'],
            'regimen_fiscal'  => $data['regimen_fiscal'],
            'domicilio_fiscal' => $data['domicilio_fiscal'],
            'uso_cfdi_default' => $data['uso_cfdi_default'],
            'es_principal'    => $esPrincipal,
            'registrado_por'  => auth()->id(),
        ]);

        return response()->json(['status' => 'success', 'mensaje' => "RFC {$rs->rfc} registrado correctamente.", 'razon_social' => $rs], 201);
    }

    /** PUT /portal/razones-sociales/{id} */
    public function updateRazonSocial(Request $request, int $id): JsonResponse
    {
        $rs = $this->razonSocialDelPadre($id);

        $data = $request->validate([
            'razon_social'     => ['required', 'string', 'max:300'],
            'regimen_fiscal'   => ['required', 'string', 'max:10'],
            'domicilio_fiscal' => ['required', 'string', 'size:5', 'regex:/^[0-9]{5}$/'],
            'uso_cfdi_default' => ['required', 'string', 'max:10'],
            'es_principal'     => ['boolean'],
        ], [
            'razon_social.required'     => 'La razón social es obligatoria.',
            'regimen_fiscal.required'   => 'El régimen fiscal es obligatorio.',
            'domicilio_fiscal.size'     => 'El código postal debe tener exactamente 5 dígitos.',
            'domicilio_fiscal.regex'    => 'El código postal debe contener solo números.',
            'uso_cfdi_default.required' => 'El uso de CFDI es obligatorio.',
        ]);

        $esPrincipal = $request->boolean('es_principal', false);
        if ($esPrincipal) {
            RazonSocialContacto::where('contacto_id', $rs->contacto_id)
                ->where('id', '!=', $id)
                ->update(['es_principal' => false]);
        }

        $rs->update([
            'razon_social'     => $data['razon_social'],
            'regimen_fiscal'   => $data['regimen_fiscal'],
            'domicilio_fiscal' => $data['domicilio_fiscal'],
            'uso_cfdi_default' => $data['uso_cfdi_default'],
            'es_principal'     => $esPrincipal,
        ]);

        return response()->json(['status' => 'success', 'mensaje' => "RFC {$rs->rfc} actualizado correctamente.", 'razon_social' => $rs->fresh()]);
    }

    /** DELETE /portal/razones-sociales/{id} */
    public function destroyRazonSocial(int $id): JsonResponse
    {
        $rs = $this->razonSocialDelPadre($id);
        $rs->update(['activo' => false]);

        return response()->json(['status' => 'success', 'mensaje' => "RFC {$rs->rfc} eliminado."]);
    }

    /** POST /portal/razones-sociales/{id}/principal */
    public function setPrincipalRazonSocial(int $id): JsonResponse
    {
        $rs = $this->razonSocialDelPadre($id);

        RazonSocialContacto::where('contacto_id', $rs->contacto_id)->update(['es_principal' => false]);
        $rs->update(['es_principal' => true]);

        return response()->json(['status' => 'success', 'mensaje' => "RFC {$rs->rfc} marcado como principal.", 'razon_social' => $rs->fresh()]);
    }

    /** Verifica que la razón social pertenezca al padre logueado. */
    private function razonSocialDelPadre(int $id): RazonSocialContacto
    {
        $contacto = auth()->user()->contactoFamiliar;

        $rs = RazonSocialContacto::where('activo', true)->findOrFail($id);

        if (! $contacto || $rs->contacto_id !== $contacto->id) {
            abort(403, 'No tienes acceso a este registro.');
        }

        return $rs;
    }

    /** POST /portal/cfdis/emitir/{pagoId} */
    public function emitirCfdi(Request $request, int $pagoId, CfdiService $cfdiService): JsonResponse
    {
        $request->validate([
            'razon_social_id' => ['nullable', 'integer', 'exists:razon_social_contacto,id'],
            'uso_cfdi'        => ['required', 'string', 'max:10'],
        ]);

        $contacto = auth()->user()->contactoFamiliar;

        $pago = Pago::with([
            'detalles.cargo.concepto',
            'detalles.cargo.inscripcion.alumno',
            'cfdis' => fn ($q) => $q->where('estado', 'vigente'),
        ])->findOrFail($pagoId);

        // Verificar que el pago pertenece a un alumno de la familia
        $alumnoIds = Alumno::where('familia_id', $contacto?->familia_id)->pluck('id');
        $perteneceAFamilia = $pago->detalles
            ->filter(fn ($d) => $alumnoIds->contains($d->cargo?->inscripcion?->alumno_id))
            ->isNotEmpty();

        if (! $perteneceAFamilia) {
            return response()->json(['status' => 'error', 'mensaje' => 'No tienes acceso a este pago.'], 403);
        }

        if ($pago->estado === 'anulado') {
            return response()->json(['status' => 'error', 'mensaje' => 'No se puede facturar un pago anulado.'], 422);
        }

        if ($pago->cfdis->isNotEmpty()) {
            return response()->json(['status' => 'error', 'mensaje' => 'Este pago ya tiene un CFDI vigente.'], 422);
        }

        if (! $this->pagoPuedeFacturarse($pago)) {
            return response()->json([
                'status'  => 'error',
                'mensaje' => 'Este pago ya no puede facturarse. Solo se permiten facturas dentro del mismo mes del pago y en un plazo máximo de 72 horas.',
            ], 422);
        }

        $razonSocialId = $request->filled('razon_social_id') ? (int) $request->razon_social_id : null;

        // Verificar que la razón social pertenece a la familia
        if ($razonSocialId) {
            $contactoIds = ContactoFamiliar::where('familia_id', $contacto?->familia_id)->pluck('id');
            $rsValida    = RazonSocialContacto::where('id', $razonSocialId)
                ->whereIn('contacto_id', $contactoIds)
                ->where('activo', true)
                ->exists();

            if (! $rsValida) {
                return response()->json(['status' => 'error', 'mensaje' => 'No tienes acceso a esa razón social.'], 403);
            }
        }

        try {
            $resultado = $cfdiService->emitirParaPago($pago, $razonSocialId, $request->uso_cfdi, now());
        } catch (\RuntimeException $e) {
            return response()->json(['status' => 'error', 'mensaje' => $e->getMessage()], 422);
        }

        return response()->json([
            'status'   => 'success',
            'mensaje'  => "CFDI emitido correctamente. Folio: {$resultado['folio']}",
            'cfdi_id'  => $resultado['cfdi']->id,
            'uuid_sat' => $resultado['cfdi']->uuid_sat,
            'folio'    => $resultado['folio'],
        ]);
    }

    public function descargarCfdi(int $cfdiId, string $formato, FacturaComService $factura): Response|RedirectResponse
    {
        if (! in_array($formato, ['pdf', 'xml'], true)) {
            abort(404);
        }

        $cfdi = Cfdi::with('pago.detalles.cargo.inscripcion')->findOrFail($cfdiId);

        $this->verificarAccesoCfdi($cfdi);

        if (! $cfdi->factura_uid) {
            return back()->with('error', 'No se puede descargar: CFDI sin UID de factura.com.');
        }

        try {
            $contenido = $factura->descargar($cfdi->factura_uid, $formato);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al descargar la factura: ' . $e->getMessage());
        }

        $nombre   = ($cfdi->folio ?? $cfdi->uuid_sat ?? "CFDI-{$cfdiId}") . ".{$formato}";
        $mimeType = $formato === 'pdf' ? 'application/pdf' : 'application/xml';

        return response($contenido, 200, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => "attachment; filename=\"{$nombre}\"",
        ]);
    }

    /** GET /portal/fotos */
    public function fotos(): View|JsonResponse
    {
        $contacto = auth()->user()->contactoFamiliar;

        if (! $contacto?->familia_id) {
            $alumnos   = collect();
            $contactos = collect();
        } else {
            $alumnos = Alumno::where('familia_id', $contacto->familia_id)
                ->orderBy('ap_paterno')
                ->get(['id', 'nombre', 'ap_paterno', 'ap_materno', 'matricula', 'foto_url']);

            $contactos = ContactoFamiliar::where('familia_id', $contacto->familia_id)
                ->orderBy('ap_paterno')
                ->get(['id', 'nombre', 'ap_paterno', 'ap_materno', 'foto_url']);
        }

        if (request()->ajax()) {
            return response()->json(compact('alumnos', 'contactos'));
        }

        return view('portal.fotos', compact('alumnos', 'contactos'));
    }

    /** POST /portal/fotos/alumno/{alumnoId} */
    public function subirFotoAlumno(Request $request, int $alumnoId): JsonResponse
    {
        $request->validate(
            ['foto' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:2048']],
            ['foto.required' => 'Selecciona una imagen.', 'foto.mimes' => 'Solo JPG, PNG o WEBP.', 'foto.max' => 'Máximo 2 MB.']
        );

        $contacto = auth()->user()->contactoFamiliar;
        $alumno   = Alumno::where('id', $alumnoId)
            ->where('familia_id', $contacto?->familia_id)
            ->firstOrFail();

        if ($alumno->foto_url) {
            Storage::disk('public')->delete($alumno->foto_url);
        }

        $ruta = $request->file('foto')->store('alumnos/fotos', 'public');
        $alumno->update(['foto_url' => $ruta]);

        return response()->json([
            'status'   => 'success',
            'mensaje'  => 'Foto de ' . $alumno->nombre . ' actualizada.',
            'foto_url' => asset('storage/' . $ruta),
        ]);
    }

    /** POST /portal/fotos/contacto/{contactoId} */
    public function subirFotoContacto(Request $request, int $contactoId): JsonResponse
    {
        $request->validate(
            ['foto' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:2048']],
            ['foto.required' => 'Selecciona una imagen.', 'foto.mimes' => 'Solo JPG, PNG o WEBP.', 'foto.max' => 'Máximo 2 MB.']
        );

        $contacto        = auth()->user()->contactoFamiliar;
        $contactoDestino = ContactoFamiliar::where('id', $contactoId)
            ->where('familia_id', $contacto?->familia_id)
            ->firstOrFail();

        if ($contactoDestino->foto_url) {
            Storage::disk('public')->delete($contactoDestino->foto_url);
        }

        $ruta = $request->file('foto')->store('contactos/fotos', 'public');
        $contactoDestino->update(['foto_url' => $ruta]);

        return response()->json([
            'status'   => 'success',
            'mensaje'  => 'Foto de ' . $contactoDestino->nombre . ' actualizada.',
            'foto_url' => asset('storage/' . $ruta),
        ]);
    }

    /**
     * Un pago puede facturarse desde el portal si se cumplen dos condiciones acumuladas:
     * 1. El pago pertenece al mes calendario actual (restricción de negocio).
     * 2. No han transcurrido más de 72 horas desde la fecha del pago (límite SAT).
     */
    private function pagoPuedeFacturarse(Pago $pago): bool
    {
        $fechaPago = Carbon::parse($pago->fecha_pago)->startOfDay();
        $ahora     = now();

        $mismoMes  = $fechaPago->month === $ahora->month && $fechaPago->year === $ahora->year;
        $dentro72h = $ahora->diffInHours($fechaPago) <= 72;

        return $mismoMes && $dentro72h;
    }

    private function verificarAccesoAlumno(int $alumnoId): void
    {
        $contacto = auth()->user()->contactoFamiliar;

        if (! $contacto?->familia_id) {
            abort(403, 'No tiene acceso a este alumno.');
        }

        $perteneceAFamilia = Alumno::where('id', $alumnoId)
            ->where('familia_id', $contacto->familia_id)
            ->exists();

        if (! $perteneceAFamilia) {
            abort(403, 'No tiene acceso a la informacion de este alumno.');
        }
    }

    private function verificarAccesoCfdi(Cfdi $cfdi): void
    {
        $contacto = auth()->user()->contactoFamiliar;

        if (! $contacto?->familia_id) {
            abort(403, 'No tiene acceso a esta factura.');
        }

        $alumnoIds = Alumno::where('familia_id', $contacto->familia_id)->pluck('id');

        $perteneceAFamilia = $cfdi->pago?->detalles
            ->filter(fn ($d) => $alumnoIds->contains($d->cargo?->inscripcion?->alumno_id))
            ->isNotEmpty() ?? false;

        if (! $perteneceAFamilia) {
            abort(403, 'No tiene acceso a esta factura.');
        }
    }

    private function alumnosDelPadre(): Collection
    {
        $contacto = auth()->user()->contactoFamiliar()->first();

        if (! $contacto?->familia_id) {
            return collect();
        }

        return Alumno::query()
            ->where('familia_id', $contacto->familia_id)
            ->where('estado', 'activo')
            ->whereHas('inscripciones', fn ($query) => $query->where('activo', true))
            ->with([
                'inscripciones' => fn ($query) => $query->where('activo', true)->latest('id'),
                'inscripciones.ciclo',
                'inscripciones.grupo.grado.nivel',
            ])
            ->get();
    }

    private function resumenFamilia(Collection $alumnos): array
    {
        $alumnoIds = $alumnos->pluck('id');

        $cargos = Cargo::query()
            ->with('detallesPagosVigentes')
            ->whereHas('inscripcion', fn ($query) => $query->whereIn('alumno_id', $alumnoIds))
            ->get();

        return [
            'hijos' => $alumnos->count(),
            'inscritos' => $alumnos->filter(fn (Alumno $alumno) => $alumno->inscripciones->where('activo', true)->isNotEmpty())->count(),
            'total_cargado' => $cargos->sum('monto_original'),
            'total_pagado' => $cargos->sum(fn (Cargo $cargo) => $cargo->saldo_abonado),
            'total_pendiente' => $cargos->sum(fn (Cargo $cargo) => max(0, $cargo->saldo_pendiente_base)),
            'cargos_vencidos' => $cargos->filter(fn (Cargo $cargo) => str_contains($cargo->estado_real, 'vencido'))->count(),
        ];
    }
}
