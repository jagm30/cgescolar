<?php

namespace App\Http\Requests;

use App\Models\Prospecto;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProspectoEtapaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->rol, ['administrador', 'recepcion']);
    }

    public function rules(): array
    {
        return [
            'etapa' => ['required', 'in:prospecto,cita,visita,documentacion,aceptado,inscrito,no_concretado'],
            'notas' => ['required', 'string', 'min:5', 'max:1000'],
            'motivo_no_concrecion' => ['required_if:etapa,no_concretado', 'nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $prospecto = Prospecto::find($this->route('id'));

            if (!$prospecto) {
                return;
            }

            if ($prospecto->etapa === 'inscrito') {
                $validator->errors()->add('etapa', 'Este prospecto ya fue inscrito como alumno. No se puede modificar su etapa.');
            }

            if ($this->etapa === 'inscrito' && !$prospecto->alumno_id) {
                $validator->errors()->add('etapa', 'Para marcar como inscrito primero debe completar el registro del alumno desde el modulo de alumnos.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'etapa.required' => 'La nueva etapa es obligatoria.',
            'etapa.in' => 'La etapa seleccionada no es valida.',
            'notas.required' => 'Debe agregar una nota al cambio de etapa.',
            'notas.min' => 'La nota debe tener al menos 5 caracteres.',
            'motivo_no_concrecion.required_if' => 'Debe indicar el motivo por el que no se concreto la inscripcion.',
        ];
    }
}
