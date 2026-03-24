<?php

namespace App\Http\Requests;

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
            'alumno_id'        => ['required', 'exists:alumno,id'],
            'ciclo_id'         => ['required', 'exists:ciclo_escolar,id'],
            'concepto_id'      => ['required', 'exists:concepto_cobro,id'],
            'vigencia_inicio'  => ['required', 'date'],
            'vigencia_fin'     => ['nullable', 'date', 'after:vigencia_inicio'],
            'motivo'           => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // El concepto debe tener aplica_beca = true
            $concepto = \App\Models\ConceptoCobro::find($this->concepto_id);
            if ($concepto && !$concepto->aplica_beca) {
                $validator->errors()->add('concepto_id', "El concepto '{$concepto->nombre}' no permite becas. Solo aplican conceptos de tipo colegiatura.");
            }

            // Verificar que no exista ya una beca activa para este alumno,
            // concepto y ciclo con el mismo catálogo
            $existe = \App\Models\BecaAlumno::where('alumno_id', $this->alumno_id)
                ->where('catalogo_beca_id', $this->catalogo_beca_id)
                ->where('concepto_id', $this->concepto_id)
                ->where('ciclo_id', $this->ciclo_id)
                ->where('activo', true)
                ->exists();

            if ($existe) {
                $validator->errors()->add('catalogo_beca_id', 'Este alumno ya tiene asignada esta beca para el mismo concepto y ciclo escolar.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'catalogo_beca_id.required' => 'Debe seleccionar el tipo de beca del catálogo.',
            'alumno_id.required'        => 'Debe seleccionar el alumno.',
            'ciclo_id.required'         => 'Debe seleccionar el ciclo escolar.',
            'concepto_id.required'      => 'Debe seleccionar el concepto sobre el que aplica la beca.',
            'vigencia_inicio.required'  => 'La fecha de inicio de vigencia es obligatoria.',
            'vigencia_fin.after'        => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ];
    }
}
