<?php

namespace App\Http\Controllers;

use App\Enums\TipoInscripcion;
use App\Models\Alumno;
use App\Models\Auditoria;
use App\Models\CicloEscolar;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReinscripcionController extends Controller
{
    /** GET /reinscripciones */
    public function index(): \Illuminate\View\View
    {
        $ciclos = CicloEscolar::whereIn('estado', ['activo', 'configuracion'])
            ->orderByDesc('fecha_inicio')
            ->get();

        $grados = Grado::with('nivel')
            ->orderBy('nivel_id')
            ->orderBy('numero')
            ->get();

        return view('reinscripciones.index', compact('ciclos', 'grados'));
    }

    /** GET /reinscripciones/buscar — AJAX */
    public function buscar(Request $request): \Illuminate\Http\JsonResponse
    {
        $q = $request->get('q', '');

        $alumnos = Alumno::query()
            ->where(fn ($query) => $query
                ->where('nombre', 'like', "%{$q}%")
                ->orWhere('ap_paterno', 'like', "%{$q}%")
                ->orWhere('ap_materno', 'like', "%{$q}%")
                ->orWhere('matricula', 'like', "%{$q}%")
            )
            ->whereIn('estado', ['activo', 'baja_temporal', 'egresado', 'baja_definitiva'])
            ->with(['inscripciones' => fn ($q) => $q->where('activo', true)->with('ciclo', 'grupo.grado.nivel')])
            ->orderBy('ap_paterno')
            ->orderBy('nombre')
            ->limit(10)
            ->get()
            ->map(function ($a) {
                $insc = $a->inscripciones->sortByDesc('id')->first(fn ($i) => $i->grupo_id !== null);

                return [
                    'id'              => $a->id,
                    'matricula'       => $a->matricula,
                    'nombre_completo' => trim("{$a->ap_paterno} {$a->ap_materno}, {$a->nombre}"),
                    'estado'          => $a->estado,
                    'inscripcion_actual' => $insc ? [
                        'ciclo' => $insc->ciclo?->nombre,
                        'grupo' => $insc->grupo?->nombre_completo,
                    ] : null,
                ];
            });

        return response()->json($alumnos);
    }

    /** POST /reinscripciones */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'alumno_id' => 'required|exists:alumno,id',
            'ciclo_id'  => 'required|exists:ciclo_escolar,id',
            'grado_id'  => 'required|exists:grado,id',
            'grupo_id'  => 'required|exists:grupo,id',
        ], [
            'alumno_id.required' => 'Debes seleccionar un alumno.',
            'ciclo_id.required'  => 'Debes seleccionar el ciclo escolar.',
            'grado_id.required'  => 'Debes seleccionar el grado.',
            'grupo_id.required'  => 'Debes seleccionar el grupo.',
        ]);

        $alumno = Alumno::findOrFail($data['alumno_id']);
        $ciclo  = CicloEscolar::findOrFail($data['ciclo_id']);
        $grupo  = Grupo::with('grado.nivel')->findOrFail($data['grupo_id']);

        // Validar que el grupo pertenezca al ciclo y grado seleccionados
        if ((int) $grupo->ciclo_id !== (int) $data['ciclo_id'] ||
            (int) $grupo->grado_id !== (int) $data['grado_id']) {
            return back()->withInput()->with('error', 'El grupo seleccionado no corresponde al ciclo y grado indicados.');
        }

        // Validar que no exista ya una inscripción activa en ese ciclo
        $yaInscrito = $alumno->inscripciones()
            ->where('ciclo_id', $data['ciclo_id'])
            ->where('activo', true)
            ->exists();

        if ($yaInscrito) {
            return back()->withInput()->with('error',
                "El alumno {$alumno->nombre} {$alumno->ap_paterno} ya tiene una inscripción activa en el ciclo '{$ciclo->nombre}'."
            );
        }

        DB::transaction(function () use ($data, $alumno, $ciclo) {
            $inscripcion = Inscripcion::create([
                'alumno_id' => $alumno->id,
                'ciclo_id'  => $data['ciclo_id'],
                'grupo_id'  => $data['grupo_id'],
                'fecha'     => now()->toDateString(),
                'activo'    => true,
                'tipo'      => TipoInscripcion::Regular,
            ]);

            // Reactivar alumno si estaba de baja
            if (in_array($alumno->estado, ['baja_temporal', 'baja_definitiva', 'egresado'])) {
                $alumno->update(['estado' => 'activo', 'fecha_baja' => null]);

                $alumno->historialBajas()
                    ->whereNull('fecha_reactivacion')
                    ->latest('fecha_baja')
                    ->first()
                    ?->update(['fecha_reactivacion' => today()]);
            }

            Auditoria::registrar('inscripcion', $inscripcion->id, 'insert', null, $inscripcion->toArray());
        });

        return redirect()
            ->route('reinscripciones.index')
            ->with('success',
                "Alumno {$alumno->nombre} {$alumno->ap_paterno} reinscrito al ciclo '{$ciclo->nombre}' — {$grupo->nombre_completo}."
            );
    }
}
