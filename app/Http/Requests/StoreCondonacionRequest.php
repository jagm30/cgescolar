<?php

namespace App\Http\Requests;

use App\Models\Cargo;
use App\Models\DescuentoCargo;
use Illuminate\Foundation\Http\FormRequest;

class StoreCondonacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->esAdministrador();
    }

    public function rules(): array
    {
        return [
            'alumno_id' => ['required', 'integer', 'exists:alumno,id'],
            'ciclo_id' => ['required', 'integer', 'exists:ciclo_escolar,id'],
            'motivo' => ['required', 'string', 'min:10', 'max:1000'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.cargo_id' => ['required', 'integer', 'exists:cargo,id', 'distinct'],
            'detalles.*.monto' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $alumnoId = $this->input('alumno_id');
            $cicloId = $this->input('ciclo_id');

            foreach ($this->input('detalles', []) as $index => $item) {
                $cargo = Cargo::with('inscripcion')->find($item['cargo_id']);

                if (! $cargo) {
                    continue;
                }

                // El cargo debe pertenecer al alumno en el ciclo indicado
                if ($cargo->inscripcion?->alumno_id != $alumnoId
                    || $cargo->inscripcion?->ciclo_id != $cicloId) {
                    $validator->errors()->add(
                        "detalles.{$index}.cargo_id",
                        'El cargo no pertenece al alumno o ciclo seleccionado.'
                    );

                    continue;
                }

                // No se puede condonar un cargo ya pagado o condonado
                if (in_array($cargo->estado, ['pagado', 'condonado'], true)) {
                    $validator->errors()->add(
                        "detalles.{$index}.cargo_id",
                        "El cargo \"{$cargo->etiqueta}\" ya está {$cargo->estado} y no puede condonarse."
                    );

                    continue;
                }

                // El monto no debe exceder el saldo pendiente neto
                $totalDescuentosPrevios = (float) DescuentoCargo::where('cargo_id', $cargo->id)
                    ->sum('monto_aplicado');

                $saldoNetoPendiente = round(
                    (float) $cargo->monto_original - $cargo->saldo_abonado - $totalDescuentosPrevios,
                    2
                );

                if ((float) $item['monto'] > $saldoNetoPendiente) {
                    $validator->errors()->add(
                        "detalles.{$index}.monto",
                        "El monto excede el saldo pendiente de \"{$cargo->etiqueta}\" ($".number_format($saldoNetoPendiente, 2).').'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'alumno_id.required' => 'Selecciona un alumno.',
            'alumno_id.exists' => 'El alumno seleccionado no existe.',
            'ciclo_id.required' => 'El ciclo escolar es obligatorio.',
            'ciclo_id.exists' => 'El ciclo escolar seleccionado no existe.',
            'motivo.required' => 'El motivo es obligatorio.',
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres.',
            'motivo.max' => 'El motivo no puede exceder 1000 caracteres.',
            'detalles.required' => 'Debes seleccionar al menos un cargo.',
            'detalles.min' => 'Debes seleccionar al menos un cargo.',
            'detalles.*.cargo_id.required' => 'El cargo es obligatorio.',
            'detalles.*.cargo_id.exists' => 'El cargo seleccionado no existe.',
            'detalles.*.cargo_id.distinct' => 'No puedes repetir el mismo cargo.',
            'detalles.*.monto.required' => 'El monto a condonar es obligatorio.',
            'detalles.*.monto.numeric' => 'El monto debe ser un número.',
            'detalles.*.monto.min' => 'El monto mínimo a condonar es $0.01.',
        ];
    }
}
