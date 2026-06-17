<?php

namespace App\Http\Requests;

use App\Models\AsignacionPlan;
use App\Models\BecaAlumno;
use App\Models\Inscripcion;
use App\Models\PlanPago;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;

class StoreBecaAlumnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->rol === 'administrador';
    }

    public function rules(): array
    {
        return [
            'catalogo_beca_id' => ['required', 'exists:catalogo_beca,id'],
            'alumno_id' => ['required', 'exists:alumno,id'],
            'ciclo_id' => ['required', 'exists:ciclo_escolar,id'],
            'plan_id' => ['required', 'exists:plan_pago,id'],
            'vigencia_inicio' => ['required', 'date'],
            'vigencia_fin' => ['required', 'date', 'after:vigencia_inicio'],
            'motivo' => ['nullable', 'string', 'max:500'],
            'deshabilitar_beca_anterior' => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $plan = PlanPago::with('planPagoConceptos')->find($this->plan_id);

            if ($plan && (int) $plan->ciclo_id !== (int) $this->ciclo_id) {
                $validator->errors()->add('plan_id', "El plan '{$plan->nombre}' no pertenece al ciclo escolar seleccionado.");
            }

            if ($plan && $plan->planPagoConceptos->isEmpty()) {
                $validator->errors()->add('plan_id', "El plan '{$plan->nombre}' no tiene conceptos configurados.");
            }

            $inscripcion = Inscripcion::with('grupo.grado')
                ->where('alumno_id', $this->alumno_id)
                ->where('ciclo_id', $this->ciclo_id)
                ->where('activo', true)
                ->orderByRaw('grupo_id IS NULL')
                ->first();

            if (! $inscripcion) {
                $validator->errors()->add('alumno_id', 'El alumno no tiene una inscripción activa en el ciclo escolar seleccionado.');
            }

            if ($plan && $inscripcion) {
                $nivelId = $inscripcion->grupo?->grado?->nivel_id;

                $tieneAsignacion = AsignacionPlan::query()
                    ->where('plan_id', $plan->id)
                    ->where(function (Builder $query) use ($inscripcion, $nivelId) {
                        $query->where(function (Builder $subQuery) {
                            $subQuery->where('origen', 'individual')
                                ->where('alumno_id', $this->alumno_id);
                        })->orWhere(function (Builder $subQuery) use ($inscripcion) {
                            $subQuery->where('origen', 'grupo')
                                ->where('grupo_id', $inscripcion->grupo_id);
                        });

                        if ($nivelId) {
                            $query->orWhere(function (Builder $subQuery) use ($nivelId) {
                                $subQuery->where('origen', 'nivel')
                                    ->where('nivel_id', $nivelId);
                            });
                        }
                    })
                    ->exists();

                if (! $tieneAsignacion) {
                    $validator->errors()->add('plan_id', "El plan '{$plan->nombre}' no está asignado al alumno en el ciclo escolar seleccionado.");
                }
            }

            $tieneBecaActiva = BecaAlumno::where('alumno_id', $this->alumno_id)
                ->where('ciclo_id', $this->ciclo_id)
                ->where('activo', true)
                ->exists();

            if ($tieneBecaActiva && ! $this->boolean('deshabilitar_beca_anterior')) {
                $validator->errors()->add('deshabilitar_beca_anterior', 'Este alumno ya tiene una beca activa en el ciclo escolar. Marca la opción si deseas reemplazarla.');
            }

            $existe = BecaAlumno::where('alumno_id', $this->alumno_id)
                ->where('catalogo_beca_id', $this->catalogo_beca_id)
                ->where('plan_id', $this->plan_id)
                ->where('ciclo_id', $this->ciclo_id)
                ->where('activo', true)
                ->exists();

            if ($existe && ! $this->boolean('deshabilitar_beca_anterior')) {
                $validator->errors()->add('catalogo_beca_id', 'Este alumno ya tiene asignada esta beca para el mismo plan y ciclo escolar. Marca la opción para deshabilitar la beca anterior si deseas reemplazarla.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'catalogo_beca_id.required' => 'Debe seleccionar el tipo de beca del catálogo.',
            'alumno_id.required' => 'Debe seleccionar el alumno.',
            'ciclo_id.required' => 'Debe seleccionar el ciclo escolar.',
            'plan_id.required' => 'Debe seleccionar el plan de pagos sobre el que aplica la beca.',
            'vigencia_inicio.required' => 'La fecha de inicio de vigencia es obligatoria.',
            'vigencia_fin.required' => 'La fecha de fin de vigencia es obligatoria.',
            'vigencia_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ];
    }
}
