<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAlumnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->rol, ['administrador', 'recepcion']);
    }

    public function rules(): array
    {
        return [
            // Datos del alumno
            'familia_id'        => ['nullable', 'exists:familia,id'],
            'nombre'            => ['required', 'string', 'max:100'],
            'ap_paterno'        => ['required', 'string', 'max:100'],
            'ap_materno'        => ['nullable', 'string', 'max:100'],
            'fecha_nacimiento'  => ['required', 'date', 'before:today'],
            'curp'              => ['nullable', 'string', 'size:18', 'unique:alumno,curp', 'regex:/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9A-Z]{2}$/'],
            'genero'            => ['nullable', 'in:M,F,Otro'],
            'foto_url'          => ['nullable', 'string', 'max:500'],
            'observaciones'     => ['nullable', 'string', 'max:1000'],
            'fecha_inscripcion' => ['required', 'date'],

            // Inscripción
            'ciclo_id'          => ['required', 'exists:ciclo_escolar,id'],
            'grupo_id'          => ['required', 'exists:grupo,id'],

            // Familia (si es nueva)
            'apellido_familia'  => ['required_without:familia_id', 'nullable', 'string', 'max:200'],

            // Contactos (mínimo 1, máximo 3)
            'contactos'                           => ['required', 'array', 'min:1', 'max:3'],
            'contactos.*.nombre'                  => ['required', 'string', 'max:100'],
            'contactos.*.ap_paterno'              => ['nullable', 'string', 'max:100'],
            'contactos.*.ap_materno'              => ['nullable', 'string', 'max:100'],
            'contactos.*.telefono_celular'        => ['required', 'string', 'max:20'],
            'contactos.*.telefono_trabajo'        => ['nullable', 'string', 'max:20'],
            'contactos.*.email'                   => ['nullable', 'email', 'max:200'],
            'contactos.*.curp'                    => ['nullable', 'string', 'size:18'],
            'contactos.*.parentesco'              => ['required', 'in:padre,madre,abuelo,tio,otro'],
            'contactos.*.tipo'                    => ['required', 'in:padre,madre,tutor,tercero_autorizado'],
            'contactos.*.orden'                   => ['required', 'integer', 'min:1', 'max:3'],
            'contactos.*.autorizado_recoger'      => ['boolean'],
            'contactos.*.es_responsable_pago'     => ['boolean'],
            'contactos.*.tiene_acceso_portal'     => ['boolean'],

            // Prospecto origen (opcional)
            'prospecto_id' => ['nullable', 'exists:prospecto,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'           => 'El nombre del alumno es obligatorio.',
            'ap_paterno.required'       => 'El apellido paterno es obligatorio.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.before'   => 'La fecha de nacimiento debe ser anterior a hoy.',
            'curp.size'                 => 'La CURP debe tener exactamente 18 caracteres.',
            'curp.unique'               => 'Ya existe un alumno registrado con esta CURP.',
            'curp.regex'                => 'El formato de la CURP no es válido.',
            'ciclo_id.required'         => 'Debe seleccionar el ciclo escolar.',
            'grupo_id.required'         => 'Debe seleccionar el grupo.',
            'contactos.required'        => 'Debe registrar al menos un contacto familiar.',
            'contactos.min'             => 'Debe registrar al menos un contacto familiar.',
            'contactos.max'             => 'No puede registrar más de 3 contactos familiares.',
            'contactos.*.nombre.required'        => 'El nombre del contacto :position es obligatorio.',
            'contactos.*.telefono_celular.required' => 'El teléfono celular del contacto :position es obligatorio.',
            'contactos.*.parentesco.required'    => 'El parentesco del contacto :position es obligatorio.',
            'contactos.*.tipo.required'          => 'El tipo del contacto :position es obligatorio.',
        ];
    }
}
