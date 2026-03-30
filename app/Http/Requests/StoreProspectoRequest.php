<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProspectoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->rol, ['administrador', 'recepcion']);
    }

    public function rules(): array
    {
        return [
            'ciclo_id' => ['nullable', 'exists:ciclo_escolar,id'],
            'nombre' => ['required', 'string', 'max:100', "regex:/^[\p{L}\s'’-]+$/u"],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'nivel_interes_id' => ['nullable', 'exists:nivel_escolar,id'],
            'contacto_nombre' => ['required', 'string', 'max:200', "regex:/^[\p{L}\s'’-]+$/u"],
            'contacto_telefono' => ['required', 'digits:10'],
            'contacto_email' => ['nullable', 'email', 'max:200'],
            'canal_contacto' => ['nullable', 'in:referido,redes,visita_directa,web,otro'],
            'fecha_primer_contacto' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del prospecto es obligatorio.',
            'nombre.regex' => 'El nombre del prospecto solo puede contener letras, espacios, apostrofe y guion.',
            'contacto_nombre.required' => 'El nombre del contacto es obligatorio.',
            'contacto_nombre.regex' => 'El nombre del contacto solo puede contener letras, espacios, apostrofe y guion.',
            'contacto_telefono.required' => 'El telefono de contacto es obligatorio.',
            'contacto_telefono.digits' => 'El telefono debe contener exactamente 10 digitos numericos.',
            'fecha_primer_contacto.required' => 'La fecha de primer contacto es obligatoria.',
            'canal_contacto.in' => 'El canal de contacto debe ser: referido, redes, visita_directa, web u otro.',
        ];
    }
}
