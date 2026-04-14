<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->rol === 'administrador';
    }

    public function rules(): array
    {
        return [
            // Plan
            'ciclo_id'    => ['required', 'exists:ciclo_escolar,id'],
            'nivel_id'    => ['required', 'exists:nivel_escolar,id'],
            'nombre'      => ['required', 'string', 'max:200'],
            'periodicidad'=> ['required', 'in:mensual,bimestral,semestral,anual,unico'],
            'fecha_inicio'=> ['required', 'date'],
            'fecha_fin'   => ['required', 'date', 'after:fecha_inicio'],

            // Conceptos del plan (mínimo 1)
            'conceptos'             => ['required', 'array', 'min:1'],
            'conceptos.*.concepto_id' => ['required', 'exists:concepto_cobro,id'],
            'conceptos.*.monto'       => ['required', 'numeric', 'min:0.01'],

            // Política de descuento (opcional)
            'descuentos'                  => ['nullable', 'array'],
            'descuentos.*.nombre'         => ['required_with:descuentos', 'string', 'max:100'],
            'descuentos.*.tipo_valor'     => ['required_with:descuentos', 'in:porcentaje,monto_fijo'],
            'descuentos.*.valor'          => ['required_with:descuentos', 'numeric', 'min:0.01'],
            'descuentos.*.dia_limite'     => ['nullable', 'integer', 'min:1', 'max:31'],

            // Política de recargo (opcional, máximo 1)
            'recargo'                  => ['nullable', 'array'],
            'recargo.dia_limite_pago'  => ['required_with:recargo', 'integer', 'min:1', 'max:31'],
            'recargo.tipo_recargo'     => ['required_with:recargo', 'in:porcentaje,monto_fijo'],
            'recargo.valor'            => ['required_with:recargo', 'numeric', 'min:0.01'],
            'recargo.tope_maximo'      => ['nullable', 'numeric', 'min:0.01'],
        ];
    }

public function messages(): array
    {
        return [
            'ciclo_id.required'                => 'Debe seleccionar el ciclo escolar.',
            'nivel_id.required'                => 'Debe seleccionar el nivel educativo.',
            'nombre.required'                  => 'El nombre del plan es obligatorio.',
            'periodicidad.required'            => 'Debe seleccionar la periodicidad del plan.',
            'periodicidad.in'                  => 'La periodicidad debe ser: mensual, bimestral, semestral, anual o único.',
            'fecha_inicio.required'            => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required'               => 'La fecha de fin es obligatoria.',
            'fecha_fin.after'                  => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'conceptos.required'               => 'Debe agregar al menos un concepto de cobro al plan.',
            'conceptos.*.concepto_id.required' => 'Debe seleccionar el concepto de cobro.',
            'conceptos.*.monto.required'       => 'El monto del concepto es obligatorio.',
            'conceptos.*.monto.min'            => 'El monto del concepto debe ser mayor a cero.',
    
            'descuentos.*.nombre.required_with'     => 'El nombre del descuento es obligatorio.',
            'descuentos.*.tipo_valor.required_with' => 'El tipo de descuento es obligatorio.',
            'descuentos.*.valor.required_with'      => 'El valor del descuento es obligatorio.',
            'descuentos.*.valor.min'                => 'El valor del descuento debe ser mayor a cero.',

            
            'recargo.dia_limite_pago.required_with' => 'El día límite de pago es obligatorio cuando hay un recargo.',
            'recargo.tipo_recargo.required_with'    => 'El tipo de recargo es obligatorio.',
            'recargo.valor.required_with'           => 'El valor del recargo es obligatorio.',
            'recargo.dia_limite_pago.min'           => 'El día límite debe ser entre 1 y 31.',
            'recargo.valor.min'                     => 'El valor del recargo debe ser mayor a cero.',
        ];
    }
}
