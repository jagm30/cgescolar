<?php

namespace App\Http\Requests;

use App\Http\Controllers\CargoController;
use App\Models\Cargo;
use Illuminate\Foundation\Http\FormRequest;

class StorePagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->rol, ['administrador', 'caja']);
    }

    public function rules(): array
    {
        return [
            // Encabezado del pago (un solo movimiento de caja)
            'forma_pago' => ['required', 'in:efectivo,transferencia,tarjeta,cheque'],
            'referencia' => ['required_if:forma_pago,transferencia', 'nullable', 'string', 'max:100'],
            'fecha_pago' => ['required', 'date'],

            // Detalles — uno por cada cargo a pagar en esta exhibición
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.cargo_id' => ['required', 'exists:cargo,id'],
            'detalles.*.monto' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $cargoIds = collect($this->detalles ?? [])->pluck('cargo_id');

            // No puede haber cargos duplicados en la misma exhibición
            if ($cargoIds->count() !== $cargoIds->unique()->count()) {
                $validator->errors()->add('detalles', 'No puede incluir el mismo cargo más de una vez en la misma exhibición.');

                return;
            }

            foreach ($this->detalles ?? [] as $index => $detalle) {
                $cargo = Cargo::with([
                    'inscripcion',
                    'descuentos',
                    'asignacion.plan.politicasDescuentoActivas',
                    'asignacion.plan.politicaRecargoActiva',
                ])->find($detalle['cargo_id'] ?? null);

                if (! $cargo) {
                    continue;
                }

                // No se puede pagar un cargo ya pagado o condonado
                if (in_array($cargo->estado, ['pagado', 'condonado'])) {
                    $validator->errors()->add(
                        "detalles.{$index}.cargo_id",
                        "El cargo #{$cargo->id} (período {$cargo->periodo}) tiene estado '{$cargo->estado}' y no puede incluirse."
                    );
                }

                $preview = app(CargoController::class)->calcularPreviewCobro($cargo);
                $totalACobrar = $preview['total_a_cobrar'];

                if ($totalACobrar <= 0) {
                    $validator->errors()->add(
                        "detalles.{$index}.cargo_id",
                        "El cargo #{$cargo->id} ya no tiene saldo por cobrar."
                    );
                }

                // El pago no puede superar el total exigible hoy
                if (isset($detalle['monto']) && $detalle['monto'] > $totalACobrar + 0.01) {
                    $validator->errors()->add(
                        "detalles.{$index}.monto",
                        "El monto cobrado (\${$detalle['monto']}) supera el total exigible hoy (\${$totalACobrar}) del cargo #{$cargo->id}."
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'forma_pago.required' => 'Debe seleccionar la forma de pago.',
            'forma_pago.in' => 'La forma de pago debe ser: efectivo, transferencia, tarjeta o cheque.',
            'referencia.required_if' => 'El folio de referencia es obligatorio para pagos por transferencia.',
            'fecha_pago.required' => 'La fecha de pago es obligatoria.',
            'detalles.required' => 'Debe incluir al menos un cargo en el pago.',
            'detalles.min' => 'Debe incluir al menos un cargo en el pago.',
            'detalles.*.cargo_id.required' => 'El cargo del detalle :position es obligatorio.',
            'detalles.*.monto.required' => 'El monto del detalle :position es obligatorio.',
            'detalles.*.monto.min' => 'El monto del detalle :position debe ser mayor a cero.',
        ];
    }
}
