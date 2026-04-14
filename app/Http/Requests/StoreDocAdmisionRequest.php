<?php

namespace App\Http\Requests;

use App\Models\DocAdmision;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreDocAdmisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_documento' => [
                'required',
                'string',
                Rule::in($this->tiposDocumentoPermitidos()),
            ],
            'otro_documento' => ['nullable', 'string', 'min:3', 'max:120', 'required_if:tipo_documento,Otro'],
            'archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_documento.required' => 'Selecciona un tipo de documento.',
            'tipo_documento.in' => 'El tipo de documento seleccionado no es valido.',
            'otro_documento.required_if' => 'Especifica cual es el documento cuando seleccionas Otro.',
            'otro_documento.min' => 'El nombre del documento debe tener al menos 3 caracteres.',
            'otro_documento.max' => 'El nombre del documento no debe exceder 120 caracteres.',
            'archivo.required' => 'Debes seleccionar un archivo.',
            'archivo.file' => 'El archivo seleccionado no es valido.',
            'archivo.mimes' => 'El archivo debe ser PDF, JPG, JPEG, PNG, DOC o DOCX.',
            'archivo.max' => 'El archivo no debe exceder 5 MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('otro_documento')) {
            $this->merge([
                'otro_documento' => Str::squish($this->input('otro_documento')),
            ]);
        }
    }

    private function tiposDocumentoPermitidos(): array
    {
        $base = [
            'Acta de nacimiento',
            'CURP',
            'Certificado de estudios',
            'Boletas ciclo anterior',
            'Comprobante de domicilio',
            'Cartilla de vacunacion',
            'Fotos tamano infantil',
            'INE del tutor',
            'Comprobante de pago',
        ];

        $personalizados = DocAdmision::query()
            ->whereNotIn('tipo_documento', $base)
            ->where('tipo_documento', '<>', 'Otro')
            ->pluck('tipo_documento')
            ->map(fn($tipo) => Str::squish($tipo))
            ->unique(fn($tipo) => Str::lower($tipo))
            ->values()
            ->all();

        return [
            ...$base,
            ...$personalizados,
            'Otro',
        ];
    }
}
