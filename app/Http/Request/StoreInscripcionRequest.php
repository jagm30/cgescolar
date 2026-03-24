<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInscripcionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->rol, ['administrador', 'recepcion']);
    }

    public function rules(): array
    {
        return [
            'alumno_id' => ['required', 'exists:alumno,id'],
            'ciclo_id'  => ['required', 'exists:ciclo_escolar,id'],
            'grupo_id'  => ['required', 'exists:grupo,id'],
            'fecha'     => ['required', 'date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Verificar que el alumno no esté ya inscrito en este ciclo
            $existe = \App\Models\Inscripcion::where('alumno_id', $this->alumno_id)
                ->where('ciclo_id', $this->ciclo_id)
                ->exists();

            if ($existe) {
                $validator->errors()->add('alumno_id', 'El alumno ya está inscrito en este ciclo escolar.');
            }

            // Verificar que el grupo no haya superado el cupo máximo
            $grupo = \App\Models\Grupo::find($this->grupo_id);
            if ($grupo && $grupo->cupo_maximo) {
                $inscritos = \App\Models\Inscripcion::where('grupo_id', $this->grupo_id)
                    ->where('activo', true)
                    ->count();

                if ($inscritos >= $grupo->cupo_maximo) {
                    $validator->errors()->add('grupo_id', "El grupo {$grupo->nombre} ha alcanzado su cupo máximo de {$grupo->cupo_maximo} alumnos.");
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'alumno_id.required' => 'Debe seleccionar un alumno.',
            'ciclo_id.required'  => 'Debe seleccionar el ciclo escolar.',
            'grupo_id.required'  => 'Debe seleccionar el grupo.',
            'fecha.required'     => 'La fecha de inscripción es obligatoria.',
        ];
    }
}
