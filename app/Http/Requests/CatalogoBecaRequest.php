<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CatalogoBecaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->rol === 'administrador';
    }

    public function rules(): array
    {
        return [
            'nombre'      => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'tipo'        => ['required', 'in:porcentaje,monto_fijo'],
            'valor'       => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la beca es obligatorio.',
            'nombre.max'      => 'El nombre de la beca no puede superar los 100 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 500 caracteres.',
            'tipo.required'   => 'Debe seleccionar el tipo de beca.',
            'tipo.in'         => 'El tipo de beca seleccionado no es válido.',
            'valor.required'  => 'El valor de la beca es obligatorio.',
            'valor.numeric'   => 'El valor de la beca debe ser un número válido.',
            'valor.min'       => 'El valor de la beca debe ser mayor a cero.',
        ];
    }
}
