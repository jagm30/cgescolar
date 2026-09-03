<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoContacto;
use App\Models\BecaAlumno;
use App\Models\Cargo;
use App\Models\Cfdi;
use App\Models\CondicionMedica;
use App\Models\ContactoFamiliar;
use App\Models\Inscripcion;
use App\Models\MedicamentoAutorizado;
use App\Models\Pago;
use App\Models\PlanPagoConcepto;
use App\Models\RazonSocialContacto;
use App\Models\Setting;
use App\Services\CfdiService;
use App\Services\EstadoCuentaService;
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

    /** GET /portal/hijos/{alumnoId}/expediente */
    /** PATCH /portal/hijos/{alumnoId}/curp */
    public function actualizarCurp(Request $request, int $alumnoId): JsonResponse
    {
        if (! Setting::find(1)?->portal_editar_curp_habilitado) {
            return response()->json(['status' => 'error', 'mensaje' => 'Función no habilitada.'], 403);
        }

        $this->verificarAccesoAlumno($alumnoId);

        $datos = $request->validate([
            'curp' => ['nullable', 'string', 'max:18', 'regex:/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9]{2}$/'],
        ], [
            'curp.regex' => 'El formato de la CURP no es válido.',
            'curp.max' => 'La CURP no puede tener más de 18 caracteres.',
        ]);

        Alumno::findOrFail($alumnoId)->update(['curp' => $datos['curp'] ?? null]);

        return response()->json(['status' => 'success', 'mensaje' => 'CURP actualizada correctamente.']);
    }

    public function expedienteMedico(int $alumnoId): View
    {
        $this->verificarAccesoAlumno($alumnoId);

        $alumno = Alumno::with([
            'fichaMedica',
            'condicionesMedicas',
            'medicamentosAutorizados.contactoAutoriza',
        ])->findOrFail($alumnoId);

        $contacto = auth()->user()->contactoFamiliar()->first();
        $contactosFamilia = $contacto?->familia_id
            ? ContactoFamiliar::where('familia_id', $contacto->familia_id)
                ->orderBy('ap_paterno')
                ->get(['id', 'nombre', 'ap_paterno', 'ap_materno'])
            : collect();

        $fm = $alumno->fichaMedica;

        return view('portal.expediente-medico', compact('alumno', 'fm', 'contactosFamilia'));
    }

    /** POST /portal/hijos/{alumnoId}/ficha-medica */
    public function actualizarFichaMedica(Request $request, int $alumnoId): JsonResponse
    {
        if (! Setting::find(1)?->portal_editar_expediente_habilitado) {
            return response()->json(['status' => 'error', 'mensaje' => 'Función no habilitada.'], 403);
        }

        $this->verificarAccesoAlumno($alumnoId);

        $datos = $request->validate([
            'tipo_sangre' => ['nullable', 'string', 'max:5'],
            'peso_kg' => ['nullable', 'numeric', 'min:1', 'max:300'],
            'talla_cm' => ['nullable', 'numeric', 'min:30', 'max:250'],
            'medico_nombre' => ['nullable', 'string', 'max:255'],
            'medico_telefono' => ['nullable', 'string', 'max:20'],
            'hospital_preferente' => ['nullable', 'string', 'max:255'],
            'discapacidad' => ['nullable', 'string', 'max:1000'],
            'observaciones_generales' => ['nullable', 'string', 'max:2000'],
        ], [
            'peso_kg.min' => 'El peso debe ser mayor a 1 kg.',
            'peso_kg.max' => 'El peso no puede superar 300 kg.',
            'talla_cm.min' => 'La talla debe ser mayor a 30 cm.',
            'talla_cm.max' => 'La talla no puede superar 250 cm.',
        ]);

        $alumno = Alumno::findOrFail($alumnoId);
        $datos = array_merge($datos, ['actualizado_por' => auth()->id(), 'actualizado_at' => now()]);

        $ficha = $alumno->fichaMedica;
        if ($ficha) {
            $ficha->update($datos);
        } else {
            $alumno->fichaMedica()->create($datos);
        }

        return response()->json(['status' => 'success', 'mensaje' => 'Ficha médica actualizada.']);
    }

    /** POST /portal/hijos/{alumnoId}/condiciones-medicas */
    public function storeCondicion(Request $request, int $alumnoId): JsonResponse
    {
        if (! Setting::find(1)?->portal_editar_expediente_habilitado) {
            return response()->json(['status' => 'error', 'mensaje' => 'Función no habilitada.'], 403);
        }

        $this->verificarAccesoAlumno($alumnoId);

        $request->merge(['requiere_accion' => $request->boolean('requiere_accion')]);

        $datos = $request->validate([
            'tipo' => ['required', 'string', 'in:padecimiento,alergia_alimento,alergia_medicamento,alergia_ambiental,discapacidad,otro'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'nivel_riesgo' => ['required', 'string', 'in:leve,moderado,grave,critico'],
            'requiere_accion' => ['nullable', 'boolean'],
            'accion_requerida' => ['nullable', 'string', 'max:1000', 'required_if:requiere_accion,1'],
        ], [
            'tipo.required' => 'El tipo de condición es obligatorio.',
            'tipo.in' => 'Selecciona un tipo de condición válido.',
            'nombre.required' => 'El nombre de la condición es obligatorio.',
            'nivel_riesgo.required' => 'El nivel de riesgo es obligatorio.',
            'nivel_riesgo.in' => 'Selecciona un nivel de riesgo válido.',
            'accion_requerida.required_if' => 'Describe la acción a tomar si marcas que requiere intervención.',
        ]);

        $alumno = Alumno::findOrFail($alumnoId);
        $alumno->condicionesMedicas()->create($datos);

        return response()->json(['status' => 'success', 'mensaje' => 'Condición médica registrada.']);
    }

    /** DELETE /portal/condiciones-medicas/{id} */
    public function destroyCondicion(int $id): JsonResponse
    {
        if (! Setting::find(1)?->portal_editar_expediente_habilitado) {
            return response()->json(['status' => 'error', 'mensaje' => 'Función no habilitada.'], 403);
        }

        $condicion = CondicionMedica::findOrFail($id);
        $this->verificarAccesoAlumno($condicion->alumno_id);
        $condicion->delete();

        return response()->json(['status' => 'success', 'mensaje' => 'Condición médica eliminada.']);
    }

    /** POST /portal/hijos/{alumnoId}/medicamentos */
    public function storeMedicamento(Request $request, int $alumnoId): JsonResponse
    {
        if (! Setting::find(1)?->portal_editar_expediente_habilitado) {
            return response()->json(['status' => 'error', 'mensaje' => 'Función no habilitada.'], 403);
        }

        $this->verificarAccesoAlumno($alumnoId);

        $request->merge(['requiere_refrigeracion' => $request->boolean('requiere_refrigeracion')]);

        $datos = $request->validate([
            'autorizado_por_contacto' => ['required', 'exists:contacto_familiar,id'],
            'nombre_medicamento' => ['required', 'string', 'max:255'],
            'dosis' => ['required', 'string', 'max:255'],
            'frecuencia' => ['required', 'string', 'max:255'],
            'horario' => ['nullable', 'string', 'max:255'],
            'requiere_refrigeracion' => ['nullable', 'boolean'],
            'instrucciones' => ['nullable', 'string', 'max:1000'],
            'vigencia_fin' => ['nullable', 'date', 'after:today'],
        ], [
            'autorizado_por_contacto.required' => 'Selecciona el contacto que autoriza el medicamento.',
            'autorizado_por_contacto.exists' => 'El contacto seleccionado no existe.',
            'nombre_medicamento.required' => 'El nombre del medicamento es obligatorio.',
            'dosis.required' => 'La dosis es obligatoria.',
            'frecuencia.required' => 'La frecuencia de administración es obligatoria.',
            'vigencia_fin.after' => 'La fecha de vigencia debe ser posterior a hoy.',
        ]);

        $alumno = Alumno::findOrFail($alumnoId);
        $alumno->medicamentosAutorizados()->create($datos);

        return response()->json(['status' => 'success', 'mensaje' => 'Medicamento autorizado registrado.']);
    }

    /** DELETE /portal/medicamentos/{id} */
    public function destroyMedicamento(int $id): JsonResponse
    {
        if (! Setting::find(1)?->portal_editar_expediente_habilitado) {
            return response()->json(['status' => 'error', 'mensaje' => 'Función no habilitada.'], 403);
        }

        $medicamento = MedicamentoAutorizado::findOrFail($id);
        $this->verificarAccesoAlumno($medicamento->alumno_id);
        $medicamento->delete();

        return response()->json(['status' => 'success', 'mensaje' => 'Medicamento eliminado.']);
    }

    public function estadoCuenta(int $alumnoId, EstadoCuentaService $service): View|JsonResponse|RedirectResponse
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

        // ── Becas vigentes del alumno (para proyección en cargos pendientes) ──
        $becas = BecaAlumno::with(['catalogoBeca', 'plan', 'concepto'])
            ->where('alumno_id', $alumnoId)
            ->where('activo', true)
            ->where(fn ($q) => $q->whereNull('vigencia_fin')->orWhere('vigencia_fin', '>=', now()))
            ->get();

        $becasPorPlan = $becas->whereNotNull('plan_id')->keyBy('plan_id');
        $becasPorConcepto = $becas->whereNotNull('concepto_id')->keyBy('concepto_id');
        $becasGlobales = $becas->filter(fn ($b) => $b->plan_id === null && $b->concepto_id === null);

        $hoy = today();

        $cargos = Cargo::with([
            'concepto',
            'inscripcion:id,ciclo_id',
            'detallesPagosVigentes',
            'descuentos',
            // Solo condonaciones activas (filtra las canceladas)
            'condonacionDetalles' => fn ($q) => $q->whereHas(
                'condonacion', fn ($q) => $q->where('estado', 'activa')
            ),
            'asignacion.plan.politicasDescuentoActivas',
            'asignacion.plan.politicasRecargo',
        ])
            ->where('inscripcion_id', $inscripcion->id)
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(function (Cargo $cargo) use ($service, $becasPorPlan, $becasPorConcepto, $becasGlobales, $hoy) {

                $hasPagos = $cargo->detallesPagosVigentes->isNotEmpty();
                $esCondonado = $cargo->estado === 'condonado';

                if ($hasPagos) {
                    // ── Cargo con abonos: leer valores ya registrados en pago_detalle ──
                    // descuento_otros ya incluye cualquier condonación aplicada en el cobro,
                    // por lo que NO se suma condonacion_detalle por separado (evita doble conteo).
                    $descuentoBeca = (float) $cargo->detallesPagosVigentes->sum('descuento_beca');
                    $descuentoProntoPago = (float) $cargo->detallesPagosVigentes->sum('descuento_pronto_pago');
                    $descuentoOtros = (float) $cargo->detallesPagosVigentes->sum('descuento_otros');
                    $recargoAplicado = (float) $cargo->detallesPagosVigentes->sum('recargo_aplicado');
                    $condonacion = 0.0;
                    $montoCobrado = (float) $cargo->detallesPagosVigentes->sum('monto_final');

                } elseif ($esCondonado) {
                    // ── Cargo condonado sin pagos: mostrar monto de condonacion_detalle ──
                    $descuentoBeca = $descuentoProntoPago = $descuentoOtros = $recargoAplicado = 0.0;
                    $condonacion = (float) $cargo->condonacionDetalles->sum('monto_aplicado');
                    $montoCobrado = 0.0;

                } else {
                    // ── Cargo pendiente: proyectar descuentos y recargo desde reglas de negocio ──
                    $montoOriginal = (float) $cargo->monto_original;

                    // Condonaciones activas ya registradas para este cargo
                    $condonacion = (float) $cargo->condonacionDetalles->sum('monto_aplicado');

                    // Base para calcular beca (descontando condonación ya registrada)
                    $saldoTrasCondo = max(0.0, $montoOriginal - $condonacion);

                    // Beca proyectada
                    $becaYaAplicada = (float) $cargo->detallesPagosVigentes->sum('descuento_beca');
                    [$descuentoBeca] = $service->calcularBecaCargo(
                        $cargo, $saldoTrasCondo, $becaYaAplicada,
                        $becasPorPlan, $becasPorConcepto, $becasGlobales
                    );

                    // Descuento pronto pago / recargo por mora
                    $vencido = $hoy->gt($cargo->fecha_vencimiento);
                    $saldoTrasBeca = max(0.0, $saldoTrasCondo - $descuentoBeca);

                    [$descuentoProntoPago, $recargoAplicado] = $cargo->asignacion?->plan
                        ? $service->calcularPoliticaCargo($cargo, $saldoTrasBeca, $vencido, $hoy)
                        : [0.0, 0.0];

                    $descuentoOtros = 0.0;
                    $montoCobrado = 0.0;
                }

                $montoNeto = max(0.0, (float) $cargo->monto_original
                    - $descuentoBeca
                    - $descuentoProntoPago
                    - $descuentoOtros
                    - $condonacion
                    + $recargoAplicado);

                return [
                    'id' => $cargo->id,
                    'concepto' => $cargo->concepto->nombre,
                    'periodo' => $cargo->periodo,
                    'periodo_label' => $cargo->periodo_label,
                    'monto_original' => $cargo->monto_original,
                    'monto_neto' => $montoNeto,
                    'monto_cobrado' => $montoCobrado,
                    'saldo_pendiente' => max(0.0, $montoNeto - $montoCobrado),
                    'estado' => $hasPagos || $esCondonado
                        ? $cargo->estado_real
                        : ($hoy->gt($cargo->fecha_vencimiento) ? 'vencido' : 'pendiente'),
                    'fecha_vencimiento' => $cargo->fecha_vencimiento,
                    'puede_facturar' => $hasPagos,
                    'descuento_beca' => $descuentoBeca,
                    'descuento_pronto_pago' => $descuentoProntoPago,
                    'descuento_otros' => $descuentoOtros,
                    'recargo_aplicado' => $recargoAplicado,
                    'condonacion' => $condonacion,
                ];
            });

        $totalCobrado = $cargos->sum('monto_cobrado');
        $totalPendiente = $cargos->sum('saldo_pendiente');

        $resumen = [
            'total_cargado' => $totalCobrado + $totalPendiente,
            'total_pendiente' => $totalPendiente,
            'total_pagado' => $totalCobrado,
            'total_cargos' => $cargos->count(),
            'cargos_vencidos' => $cargos->filter(fn (array $c) => str_contains($c['estado'], 'vencido'))->count(),
        ];

        $alumno = $inscripcion->alumno;

        if (request()->ajax()) {
            return response()->json(['resumen' => $resumen, 'cargos' => $cargos]);
        }

        return view('portal.estado-cuenta', compact('alumno', 'cargos', 'inscripcion', 'resumen'));
    }

    /** GET /portal/facturas — todos los pagos de todos los hijos, para facturar */
    public function facturas(): View
    {
        $alumnos = $this->alumnosDelPadre();

        $alumnosConPagos = $alumnos->map(function (Alumno $alumno) {
            $pagos = Pago::with(['detalles.cargo.concepto', 'detalles.cargo.asignacion', 'cfdis'])
                ->whereHas('detalles.cargo.inscripcion', fn ($q) => $q->where('alumno_id', $alumno->id))
                ->where('estado', 'vigente')
                ->orderByDesc('fecha_pago')
                ->get();

            $facturableMap = $this->construirMapaFacturable($pagos);

            $pagos = $pagos->map(fn (Pago $pago) => [
                'id' => $pago->id,
                'folio_recibo' => $pago->folio_recibo,
                'conceptos' => $pago->detalles->map(fn ($d) => $d->cargo->etiqueta)->join(', '),
                'monto_total' => $pago->monto_total,
                'fecha_pago' => $pago->fecha_pago,
                'forma_pago' => $pago->forma_pago,
                'tiene_factura' => $pago->cfdis->where('estado', 'vigente')->isNotEmpty(),
                'cfdi_id' => $pago->cfdis->where('estado', 'vigente')->first()?->id,
                'puede_facturar' => $this->pagoPuedeFacturarse($pago),
                'todos_facturables' => $this->todosFacturables($pago, $facturableMap),
            ]);

            return ['alumno' => $alumno, 'pagos' => $pagos];
        });

        $contacto = auth()->user()->contactoFamiliar()->with('familia')->first();
        $razonesSociales = $contacto?->familia_id
            ? RazonSocialContacto::whereIn('contacto_id',
                ContactoFamiliar::where('familia_id', $contacto->familia_id)->pluck('id')
            )
                ->where('activo', true)
                ->orderByDesc('es_principal')
                ->get(['id', 'rfc', 'razon_social', 'uso_cfdi_default', 'es_principal'])
            : collect();

        return view('portal.facturas', ['alumnosConPagos' => $alumnosConPagos, 'razonesSociales' => $razonesSociales]);
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
                'tiene_factura' => $pago->cfdis->where('estado', 'vigente')->isNotEmpty(),
                'cfdi_id' => $pago->cfdis->where('estado', 'vigente')->first()?->id,
                'cfdi_uuid' => $pago->cfdis->where('estado', 'vigente')->first()?->uuid_sat,
                'puede_facturar' => $this->pagoPuedeFacturarse($pago),
            ]);

        $alumno = Alumno::findOrFail($alumnoId);

        if (request()->ajax()) {
            return response()->json($pagos);
        }

        $contacto = auth()->user()->contactoFamiliar()->with('familia')->first();
        $razonesSociales = $contacto?->familia_id
            ? RazonSocialContacto::whereIn('contacto_id',
                ContactoFamiliar::where('familia_id', $contacto->familia_id)->pluck('id')
            )
                ->where('activo', true)
                ->orderByDesc('es_principal')
                ->get(['id', 'rfc', 'razon_social', 'uso_cfdi_default', 'es_principal'])
            : collect();

        return view('portal.historial-pagos', compact('alumno', 'pagos', 'razonesSociales'));
    }

    /** GET /portal/familiares */
    public function familiares(): View|JsonResponse
    {
        $contacto = auth()->user()->contactoFamiliar()->with('familia')->first();

        $contactos = $contacto?->familia_id
            ? ContactoFamiliar::with(['alumnoContactos.alumno'])
                ->where('familia_id', $contacto->familia_id)
                ->orderBy('nombre')
                ->get()
            : collect();

        if (request()->ajax()) {
            return response()->json($contactos);
        }

        return view('portal.familiares', [
            'contactos' => $contactos,
            'miContactoId' => $contacto?->id,
            'familiaId' => $contacto?->familia_id,
        ]);
    }

    /** POST /portal/familiares */
    public function storeFamiliar(Request $request): JsonResponse
    {
        $contactoActual = auth()->user()->contactoFamiliar;

        if (! $contactoActual?->familia_id) {
            return response()->json(['status' => 'error', 'mensaje' => 'Sin familia asociada.'], 403);
        }

        $total = ContactoFamiliar::where('familia_id', $contactoActual->familia_id)->count();
        if ($total >= 3) {
            return response()->json([
                'status' => 'error',
                'mensaje' => 'Tu familia ya tiene 3 contactos registrados, que es el máximo permitido.',
            ], 422);
        }

        $parentescoValidos = ['padre', 'madre', 'abuelo', 'tio', 'hermano', 'tutor', 'otro'];

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'ap_paterno' => ['nullable', 'string', 'max:100'],
            'ap_materno' => ['nullable', 'string', 'max:100'],
            'parentesco' => ['required', 'string', 'in:'.implode(',', $parentescoValidos)],
            'telefono_celular' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'parentesco.required' => 'Selecciona el parentesco.',
            'parentesco.in' => 'El parentesco seleccionado no es válido.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
        ]);

        $nuevo = ContactoFamiliar::create([
            'familia_id' => $contactoActual->familia_id,
            'nombre' => $data['nombre'],
            'ap_paterno' => $data['ap_paterno'] ?? null,
            'ap_materno' => $data['ap_materno'] ?? null,
            'telefono_celular' => $data['telefono_celular'] ?? null,
            'email' => $data['email'] ?? null,
            'tiene_acceso_portal' => false,
        ]);

        $this->vincularContactoAAlumnos($nuevo, $data['parentesco'], $contactoActual->familia_id);

        return response()->json([
            'status' => 'success',
            'mensaje' => "Familiar {$nuevo->nombre_completo} agregado correctamente.",
            'contacto' => $nuevo,
        ], 201);
    }

    /** PATCH /portal/familiares/{contactoId}/autorizado-recoger/{alumnoId} */
    public function toggleAutorizadoRecoger(int $contactoId, int $alumnoId): JsonResponse
    {
        $setting = Setting::find(1);

        if (! $setting?->portal_autorizado_recoger_habilitado) {
            return response()->json(['status' => 'error', 'mensaje' => 'Función no disponible.'], 403);
        }

        $contactoActual = auth()->user()->contactoFamiliar;

        // Verificar que el contacto objetivo pertenece a la misma familia
        ContactoFamiliar::where('id', $contactoId)
            ->where('familia_id', $contactoActual?->familia_id)
            ->firstOrFail();

        $alumnoContacto = AlumnoContacto::where('contacto_id', $contactoId)
            ->where('alumno_id', $alumnoId)
            ->where('activo', true)
            ->firstOrFail();

        $alumnoContacto->autorizado_recoger = ! $alumnoContacto->autorizado_recoger;
        $alumnoContacto->save();

        return response()->json([
            'status' => 'success',
            'autorizado_recoger' => $alumnoContacto->autorizado_recoger,
            'mensaje' => $alumnoContacto->autorizado_recoger
                ? 'Autorización para recoger activada.'
                : 'Autorización para recoger desactivada.',
        ]);
    }

    public function razonesSociales(): View|JsonResponse
    {
        $contacto = auth()->user()->contactoFamiliar()->with('familia')->first();

        // Todas las razones sociales activas de todos los contactos de la familia
        $razonesSociales = $contacto?->familia_id
            ? RazonSocialContacto::with('contacto')
                ->whereIn('contacto_id',
                    ContactoFamiliar::where('familia_id', $contacto->familia_id)->pluck('id')
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
            'miContactoId' => $contacto?->id,
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
            'rfc' => ['required', 'string', 'between:12,13', 'regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/'],
            'razon_social' => ['required', 'string', 'max:300'],
            'regimen_fiscal' => ['required', 'string', 'max:10'],
            'domicilio_fiscal' => ['required', 'string', 'size:5', 'regex:/^[0-9]{5}$/'],
            'uso_cfdi_default' => ['required', 'string', 'max:10'],
            'es_principal' => ['boolean'],
        ], [
            'rfc.regex' => 'El formato del RFC no es válido.',
            'razon_social.required' => 'La razón social es obligatoria.',
            'regimen_fiscal.required' => 'El régimen fiscal es obligatorio.',
            'domicilio_fiscal.size' => 'El código postal debe tener exactamente 5 dígitos.',
            'domicilio_fiscal.regex' => 'El código postal debe contener solo números.',
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

        if (! RazonSocialContacto::usoCfdiCompatibleConRegimen($data['regimen_fiscal'], $data['uso_cfdi_default'])) {
            return response()->json([
                'status' => 'error',
                'mensaje' => 'El régimen fiscal '.$data['regimen_fiscal'].' no admite deducciones personales (D01–D10). Elige otro Uso de CFDI, como G03.',
            ], 422);
        }

        $esPrincipal = $request->boolean('es_principal', false);
        if ($esPrincipal) {
            RazonSocialContacto::where('contacto_id', $contacto->id)->update(['es_principal' => false]);
        }

        $rs = RazonSocialContacto::create([
            'contacto_id' => $contacto->id,
            'rfc' => $rfc,
            'razon_social' => $data['razon_social'],
            'regimen_fiscal' => $data['regimen_fiscal'],
            'domicilio_fiscal' => $data['domicilio_fiscal'],
            'uso_cfdi_default' => $data['uso_cfdi_default'],
            'es_principal' => $esPrincipal,
            'registrado_por' => auth()->id(),
        ]);

        return response()->json(['status' => 'success', 'mensaje' => "RFC {$rs->rfc} registrado correctamente.", 'razon_social' => $rs], 201);
    }

    /** PUT /portal/razones-sociales/{id} */
    public function updateRazonSocial(Request $request, int $id): JsonResponse
    {
        $rs = $this->razonSocialDelPadre($id);

        $data = $request->validate([
            'razon_social' => ['required', 'string', 'max:300'],
            'regimen_fiscal' => ['required', 'string', 'max:10'],
            'domicilio_fiscal' => ['required', 'string', 'size:5', 'regex:/^[0-9]{5}$/'],
            'uso_cfdi_default' => ['required', 'string', 'max:10'],
            'es_principal' => ['boolean'],
        ], [
            'razon_social.required' => 'La razón social es obligatoria.',
            'regimen_fiscal.required' => 'El régimen fiscal es obligatorio.',
            'domicilio_fiscal.size' => 'El código postal debe tener exactamente 5 dígitos.',
            'domicilio_fiscal.regex' => 'El código postal debe contener solo números.',
            'uso_cfdi_default.required' => 'El uso de CFDI es obligatorio.',
        ]);

        if (! RazonSocialContacto::usoCfdiCompatibleConRegimen($data['regimen_fiscal'], $data['uso_cfdi_default'])) {
            return response()->json([
                'status' => 'error',
                'mensaje' => 'El régimen fiscal '.$data['regimen_fiscal'].' no admite deducciones personales (D01–D10). Elige otro Uso de CFDI, como G03.',
            ], 422);
        }

        $esPrincipal = $request->boolean('es_principal', false);
        if ($esPrincipal) {
            RazonSocialContacto::where('contacto_id', $rs->contacto_id)
                ->where('id', '!=', $id)
                ->update(['es_principal' => false]);
        }

        $rs->update([
            'razon_social' => $data['razon_social'],
            'regimen_fiscal' => $data['regimen_fiscal'],
            'domicilio_fiscal' => $data['domicilio_fiscal'],
            'uso_cfdi_default' => $data['uso_cfdi_default'],
            'es_principal' => $esPrincipal,
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

    /** POST /portal/razones-sociales/{id}/constancia */
    public function subirConstancia(Request $request, int $id): JsonResponse
    {
        $rs = $this->razonSocialDelPadre($id);

        $request->validate(
            ['constancia' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048']],
            [
                'constancia.required' => 'Selecciona un archivo.',
                'constancia.mimes' => 'Solo se permiten archivos PDF, JPG o PNG.',
                'constancia.max' => 'El archivo no debe superar los 2 MB.',
            ]
        );

        if ($rs->constancia_path) {
            Storage::disk('public')->delete($rs->constancia_path);
        }

        $ruta = $request->file('constancia')->store("constancias/{$rs->contacto_id}", 'public');
        $rs->update(['constancia_path' => $ruta]);

        return response()->json([
            'status' => 'success',
            'mensaje' => 'Constancia cargada correctamente.',
            'constancia_url' => asset('storage/'.$ruta),
        ]);
    }

    /** Verifica que la razón social pertenezca al padre logueado. */
    /** Crea entradas en alumno_contacto para cada alumno activo de la familia. */
    private function vincularContactoAAlumnos(ContactoFamiliar $contacto, string $parentesco, int $familiaId): void
    {
        $tipoMap = [
            'padre' => 'padre',
            'madre' => 'madre',
            'tutor' => 'tutor',
        ];
        $tipo = $tipoMap[$parentesco] ?? 'tercero_autorizado';

        $alumnos = Alumno::where('familia_id', $familiaId)
            ->where('estado', 'activo')
            ->get();

        foreach ($alumnos as $alumno) {
            $ordenActual = AlumnoContacto::where('alumno_id', $alumno->id)->max('orden') ?? 0;

            AlumnoContacto::create([
                'alumno_id' => $alumno->id,
                'contacto_id' => $contacto->id,
                'parentesco' => $parentesco,
                'tipo' => $tipo,
                'orden' => min($ordenActual + 1, 3),
                'autorizado_recoger' => false,
                'es_responsable_pago' => false,
                'activo' => true,
            ]);
        }
    }

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
            'uso_cfdi' => ['required', 'string', 'max:10'],
        ]);

        $contacto = auth()->user()->contactoFamiliar;

        $pago = Pago::with([
            'detalles.cargo.concepto',
            'detalles.cargo.asignacion',
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
                'status' => 'error',
                'mensaje' => 'Este pago ya no puede facturarse. Solo se permiten facturas dentro del mismo mes del pago y en un plazo máximo de 72 horas.',
            ], 422);
        }

        $mapaFacturable = $this->construirMapaFacturable(collect([$pago]));
        if (! $this->todosFacturables($pago, $mapaFacturable)) {
            return response()->json([
                'status' => 'error',
                'mensaje' => 'Este pago contiene conceptos marcados como no facturables por el administrador.',
            ], 422);
        }

        $razonSocialId = $request->filled('razon_social_id') ? (int) $request->razon_social_id : null;

        // Verificar que la razón social pertenece a la familia
        if ($razonSocialId) {
            $contactoIds = ContactoFamiliar::where('familia_id', $contacto?->familia_id)->pluck('id');
            $rsValida = RazonSocialContacto::where('id', $razonSocialId)
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
            'status' => 'success',
            'mensaje' => "CFDI emitido correctamente. Folio: {$resultado['folio']}",
            'cfdi_id' => $resultado['cfdi']->id,
            'uuid_sat' => $resultado['cfdi']->uuid_sat,
            'folio' => $resultado['folio'],
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
            return back()->with('error', 'Error al descargar la factura: '.$e->getMessage());
        }

        $nombre = ($cfdi->folio ?? $cfdi->uuid_sat ?? "CFDI-{$cfdiId}").".{$formato}";
        $mimeType = $formato === 'pdf' ? 'application/pdf' : 'application/xml';

        return response($contenido, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => "attachment; filename=\"{$nombre}\"",
        ]);
    }

    /** GET /portal/fotos */
    public function fotos(): View|JsonResponse
    {
        $contacto = auth()->user()->contactoFamiliar;

        if (! $contacto) {
            $alumnos = collect();
            $contactos = collect();
        } else {
            $alumnos = Alumno::query()
                ->whereHas('contactos', fn ($q) => $q
                    ->where('contacto_familiar.id', $contacto->id)
                    ->where('alumno_contacto.tiene_acceso_portal', true)
                    ->where('alumno_contacto.activo', true)
                )
                ->orderBy('ap_paterno')
                ->get(['id', 'nombre', 'ap_paterno', 'ap_materno', 'matricula', 'foto_url']);

            $contactos = $contacto->familia_id
                ? ContactoFamiliar::where('familia_id', $contacto->familia_id)
                    ->orderBy('ap_paterno')
                    ->get(['id', 'nombre', 'ap_paterno', 'ap_materno', 'foto_url'])
                : collect();
        }

        if (request()->ajax()) {
            return response()->json(compact('alumnos', 'contactos'));
        }

        return view('portal.fotos', compact('alumnos', 'contactos'));
    }

    /** POST /portal/fotos/alumno/{alumnoId} */
    public function subirFotoAlumno(Request $request, int $alumnoId): JsonResponse
    {
        if (! Setting::find(1)?->portal_fotos_habilitado) {
            return response()->json(['status' => 'error', 'mensaje' => 'La carga de fotografías no está disponible.'], 403);
        }

        $request->validate(
            ['foto' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:2048']],
            ['foto.required' => 'Selecciona una imagen.', 'foto.mimes' => 'Solo JPG, PNG o WEBP.', 'foto.max' => 'Máximo 2 MB.']
        );

        $contacto = auth()->user()->contactoFamiliar;
        $alumno = Alumno::where('id', $alumnoId)
            ->whereHas('contactos', fn ($q) => $q
                ->where('contacto_familiar.id', $contacto?->id)
                ->where('alumno_contacto.tiene_acceso_portal', true)
                ->where('alumno_contacto.activo', true)
            )
            ->firstOrFail();

        if ($alumno->foto_url) {
            Storage::disk('public')->delete($alumno->foto_url);
        }

        $ruta = $request->file('foto')->store('alumnos/fotos', 'public');
        $alumno->update(['foto_url' => $ruta]);

        return response()->json([
            'status' => 'success',
            'mensaje' => 'Foto de '.$alumno->nombre.' actualizada.',
            'foto_url' => asset('storage/'.$ruta),
        ]);
    }

    /** POST /portal/fotos/contacto/{contactoId} */
    public function subirFotoContacto(Request $request, int $contactoId): JsonResponse
    {
        if (! Setting::find(1)?->portal_fotos_habilitado) {
            return response()->json(['status' => 'error', 'mensaje' => 'La carga de fotografías no está disponible.'], 403);
        }

        $request->validate(
            ['foto' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:2048']],
            ['foto.required' => 'Selecciona una imagen.', 'foto.mimes' => 'Solo JPG, PNG o WEBP.', 'foto.max' => 'Máximo 2 MB.']
        );

        $usuarioActual = auth()->user();
        $contacto = $usuarioActual->contactoFamiliar;
        $contactoDestino = ContactoFamiliar::where('id', $contactoId)
            ->where('familia_id', $contacto?->familia_id)
            ->firstOrFail();

        if ($contactoDestino->foto_url) {
            Storage::disk('public')->delete($contactoDestino->foto_url);
        }

        $ruta = $request->file('foto')->store('contactos/fotos', 'public');
        $contactoDestino->update(['foto_url' => $ruta]);

        // Si el contacto actualizado es el propio usuario logueado, sincronizar foto de perfil
        if ($contactoDestino->usuario_id === $usuarioActual->id) {
            if ($usuarioActual->foto_perfil && $usuarioActual->foto_perfil !== $ruta) {
                Storage::disk('public')->delete($usuarioActual->foto_perfil);
            }
            $usuarioActual->update(['foto_perfil' => $ruta]);
        }

        return response()->json([
            'status' => 'success',
            'mensaje' => 'Foto de '.$contactoDestino->nombre.' actualizada.',
            'foto_url' => asset('storage/'.$ruta),
        ]);
    }

    /**
     * Construye un mapa ["{plan_id}-{concepto_id}" => bool] con el estado facturable
     * de todos los pares plan/concepto presentes en la colección de pagos.
     * Realiza una sola consulta para evitar N+1.
     */
    private function construirMapaFacturable(Collection $pagos): Collection
    {
        $pares = $pagos->flatMap(fn ($p) => $p->detalles->map(fn ($d) => [
            'plan_id' => $d->cargo?->asignacion?->plan_id,
            'concepto_id' => $d->cargo?->concepto_id,
        ]))->filter(fn ($par) => $par['plan_id'] && $par['concepto_id']);

        if ($pares->isEmpty()) {
            return collect();
        }

        return PlanPagoConcepto::whereIn('plan_id', $pares->pluck('plan_id')->unique())
            ->whereIn('concepto_id', $pares->pluck('concepto_id')->unique())
            ->get(['plan_id', 'concepto_id', 'facturable'])
            ->keyBy(fn ($r) => "{$r->plan_id}-{$r->concepto_id}")
            ->map(fn ($r) => (bool) $r->facturable);
    }

    /**
     * Devuelve true si todos los cargos del pago están marcados como facturables.
     * Verifica dos niveles:
     *   1. concepto_cobro.facturable — el concepto en sí está habilitado para facturar.
     *   2. plan_pago_concepto.facturable — el par plan/concepto está habilitado.
     * Los cargos sin asignación de plan pasan solo la validación del concepto.
     */
    private function todosFacturables(Pago $pago, Collection $facturableMap): bool
    {
        return $pago->detalles->every(function ($d) use ($facturableMap) {
            // 1. El concepto mismo debe estar marcado como facturable en concepto_cobro
            if ($d->cargo?->concepto && ! $d->cargo->concepto->facturable) {
                return false;
            }

            $planId = $d->cargo?->asignacion?->plan_id;
            $conceptoId = $d->cargo?->concepto_id;

            if (! $planId || ! $conceptoId) {
                return true;
            }

            // 2. El par plan/concepto también debe estar habilitado en plan_pago_concepto
            return $facturableMap->get("{$planId}-{$conceptoId}", true);
        });
    }

    /**
     * Un pago puede facturarse desde el portal si se cumplen dos condiciones acumuladas:
     * 1. El pago pertenece al mes calendario actual (restricción de negocio).
     * 2. No han transcurrido más de 72 horas desde la fecha del pago (límite SAT).
     */
    private function pagoPuedeFacturarse(Pago $pago): bool
    {
        $fechaPago = Carbon::parse($pago->fecha_pago)->startOfDay();
        $ahora = now();

        $mismoMes = $fechaPago->month === $ahora->month && $fechaPago->year === $ahora->year;
        $dentro72h = $ahora->diffInHours($fechaPago) <= 72;

        return $mismoMes && $dentro72h;
    }

    private function verificarAccesoAlumno(int $alumnoId): void
    {
        $contacto = auth()->user()->contactoFamiliar;

        if (! $contacto) {
            abort(403, 'No tiene acceso a este alumno.');
        }

        $tieneAcceso = Alumno::where('id', $alumnoId)
            ->whereHas('contactos', fn ($q) => $q
                ->where('contacto_familiar.id', $contacto->id)
                ->where('alumno_contacto.tiene_acceso_portal', true)
                ->where('alumno_contacto.activo', true)
            )
            ->exists();

        if (! $tieneAcceso) {
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

        if (! $contacto) {
            return collect();
        }

        return Alumno::query()
            ->where('estado', 'activo')
            ->whereHas('contactos', fn ($q) => $q
                ->where('contacto_familiar.id', $contacto->id)
                ->where('alumno_contacto.tiene_acceso_portal', true)
                ->where('alumno_contacto.activo', true)
            )
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
            ->with(['detallesPagosVigentes', 'condonacionDetalles'])
            ->whereHas('inscripcion', fn ($query) => $query->whereIn('alumno_id', $alumnoIds))
            ->get();

        $totalCobrado = 0.0;
        $totalPendiente = 0.0;
        $totalVencido = 0.0;
        $vencidos = 0;

        foreach ($cargos as $cargo) {
            $descuentos = (float) $cargo->detallesPagosVigentes->sum('descuento_beca')
                         + (float) $cargo->detallesPagosVigentes->sum('descuento_pronto_pago')
                         + (float) $cargo->detallesPagosVigentes->sum('descuento_otros');
            $recargo = (float) $cargo->detallesPagosVigentes->sum('recargo_aplicado');
            $condonacion = (float) $cargo->condonacionDetalles->sum('monto_aplicado');
            $cobrado = (float) $cargo->detallesPagosVigentes->sum('monto_final');

            $neto = $cargo->monto_original - $descuentos - $condonacion + $recargo;
            $pendiente = max(0.0, $neto - $cobrado);

            $totalCobrado += $cobrado;
            $totalPendiente += $pendiente;

            if (str_contains($cargo->estado_real, 'vencido')) {
                $vencidos++;
                $totalVencido += $pendiente;
            }
        }

        return [
            'hijos' => $alumnos->count(),
            'inscritos' => $alumnos->filter(fn (Alumno $alumno) => $alumno->inscripciones->where('activo', true)->isNotEmpty())->count(),
            'total_cargado' => $totalCobrado + $totalPendiente,
            'total_pagado' => $totalCobrado,
            'total_pendiente' => $totalPendiente,
            'total_vencido' => $totalVencido,
            'cargos_vencidos' => $vencidos,
        ];
    }
}
