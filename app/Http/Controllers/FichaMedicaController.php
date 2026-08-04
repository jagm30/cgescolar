<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCondicionMedicaRequest;
use App\Http\Requests\StoreFichaMedicaRequest;
use App\Http\Requests\StoreMedicamentoAutorizadoRequest;
use App\Models\Alumno;
use App\Models\Auditoria;
use App\Models\CondicionMedica;
use App\Models\MedicamentoAutorizado;
use App\Traits\RespondsWithJson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FichaMedicaController extends Controller
{
    use RespondsWithJson;

    // ── Ficha médica general ─────────────────────────────

    /** POST /alumnos/{id}/ficha-medica — crea o actualiza la ficha general */
    public function storeOrUpdate(StoreFichaMedicaRequest $request, int $alumnoId): RedirectResponse
    {
        $alumno = Alumno::findOrFail($alumnoId);

        $datos = array_merge($request->validated(), [
            'actualizado_por' => auth()->id(),
            'actualizado_at'  => now(),
        ]);

        $ficha    = $alumno->fichaMedica;
        $anterior = $ficha?->toArray();

        if ($ficha) {
            $ficha->update($datos);
        } else {
            $ficha = $alumno->fichaMedica()->create($datos);
        }

        Auditoria::registrar(
            'ficha_medica',
            $ficha->id,
            $anterior ? 'update' : 'insert',
            $anterior,
            $ficha->fresh()->toArray()
        );

        return redirect()
            ->route('alumnos.show', $alumnoId)
            ->with('success', 'Ficha médica actualizada correctamente.');
    }

    // ── Condiciones médicas ──────────────────────────────

    /** POST /alumnos/{id}/condiciones-medicas */
    public function storeCondicion(StoreCondicionMedicaRequest $request, int $alumnoId): RedirectResponse
    {
        $alumno    = Alumno::findOrFail($alumnoId);
        $condicion = $alumno->condicionesMedicas()->create($request->validated());

        Auditoria::registrar('condicion_medica', $condicion->id, 'insert', null, $condicion->toArray());

        return redirect()
            ->route('alumnos.show', $alumnoId)
            ->with('success', 'Condición médica registrada.');
    }

    /** DELETE /condiciones-medicas/{id} */
    public function destroyCondicion(int $id): RedirectResponse
    {
        $condicion = CondicionMedica::findOrFail($id);
        $alumnoId  = $condicion->alumno_id;

        Auditoria::registrar('condicion_medica', $condicion->id, 'delete', $condicion->toArray(), null);
        $condicion->delete();

        return redirect()
            ->route('alumnos.show', $alumnoId)
            ->with('success', 'Condición médica eliminada.');
    }

    // ── Medicamentos autorizados ─────────────────────────

    /** POST /alumnos/{id}/medicamentos-autorizados */
    public function storeMedicamento(StoreMedicamentoAutorizadoRequest $request, int $alumnoId): RedirectResponse
    {
        $alumno      = Alumno::findOrFail($alumnoId);
        $medicamento = $alumno->medicamentosAutorizados()->create($request->validated());

        Auditoria::registrar('medicamento_autorizado', $medicamento->id, 'insert', null, $medicamento->toArray());

        return redirect()
            ->route('alumnos.show', $alumnoId)
            ->with('success', 'Medicamento autorizado registrado.');
    }

    /** DELETE /medicamentos-autorizados/{id} */
    public function destroyMedicamento(int $id): RedirectResponse
    {
        $medicamento = MedicamentoAutorizado::findOrFail($id);
        $alumnoId    = $medicamento->alumno_id;

        Auditoria::registrar('medicamento_autorizado', $medicamento->id, 'delete', $medicamento->toArray(), null);
        $medicamento->delete();

        return redirect()
            ->route('alumnos.show', $alumnoId)
            ->with('success', 'Medicamento eliminado.');
    }
}
