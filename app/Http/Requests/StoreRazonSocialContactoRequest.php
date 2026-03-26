<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRazonSocialContactoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->rol, ['administrador', 'caja']);
    }

    public function rules(): array
    {
        return [
            'contacto_id'      => ['required', 'exists:contacto_familiar,id'],
            'rfc'              => ['required', 'string', 'size:12,13',
                                   'regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/'],
            'razon_social'     => ['required', 'string', 'max:300'],
            'regimen_fiscal'   => ['required', 'string', 'max:10'],
            'domicilio_fiscal' => ['required', 'string', 'size:5', 'regex:/^[0-9]{5}$/'],
            'uso_cfdi_default' => ['required', 'string', 'max:10'],
            'es_principal'     => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Máximo 3 RFCs por contacto
            $total = \App\Models\RazonSocialContacto::where('contacto_id', $this->contacto_id)
                ->where('activo', true)
                ->count();

            if ($total >= 3) {
                $validator->errors()->add('contacto_id', 'Este contacto ya tiene 3 razones sociales registradas, que es el máximo permitido.');
            }

            // Verificar que el RFC no esté ya registrado para este contacto
            $existe = \App\Models\RazonSocialContacto::where('contacto_id', $this->contacto_id)
                ->where('rfc', strtoupper($this->rfc))
                ->exists();

            if ($existe) {
                $validator->errors()->add('rfc', 'Este RFC ya está registrado para este contacto.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'contacto_id.required'      => 'Debe seleccionar el contacto familiar.',
            'rfc.required'              => 'El RFC es obligatorio.',
            'rfc.regex'                 => 'El formato del RFC no es válido.',
            'razon_social.required'     => 'La razón social es obligatoria.',
            'regimen_fiscal.required'   => 'El régimen fiscal es obligatorio.',
            'domicilio_fiscal.required' => 'El código postal del domicilio fiscal es obligatorio.',
            'domicilio_fiscal.size'     => 'El código postal debe tener exactamente 5 dígitos.',
            'domicilio_fiscal.regex'    => 'El código postal debe contener solo números.',
            'uso_cfdi_default.required' => 'El uso de CFDI es obligatorio.',
        ];
    }
}
