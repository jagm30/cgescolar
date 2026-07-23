<?php

use App\Models\Alumno;
use App\Models\AsignacionPlan;
use App\Models\Cargo;
use App\Models\CicloEscolar;
use App\Models\ConceptoCobro;
use App\Models\DescuentoCargo;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\NivelEscolar;
use App\Models\PlanPago;
use App\Models\PlanPagoConcepto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function crearContextoCobroConCondonacion(float $montoOriginal, float $montoCondonacion): array
{
    $admin = Usuario::create([
        'nombre' => 'Admin Caja',
        'email' => fake()->unique()->safeEmail(),
        'password_hash' => bcrypt('password'),
        'rol' => 'administrador',
        'activo' => true,
    ]);

    $ciclo = CicloEscolar::create([
        'nombre' => '2026-2027',
        'fecha_inicio' => '2026-08-01',
        'fecha_fin' => '2027-07-31',
        'estado' => 'activo',
    ]);

    $nivel = NivelEscolar::create([
        'nombre' => 'Maternal',
        'revoe' => fake()->unique()->numerify('REV####'),
        'orden' => 1,
        'activo' => true,
    ]);

    $grado = Grado::create(['nivel_id' => $nivel->id, 'numero' => 1]);

    $grupo = Grupo::create([
        'ciclo_id' => $ciclo->id,
        'grado_id' => $grado->id,
        'nombre' => 'A',
        'activo' => true,
    ]);

    $alumno = Alumno::create([
        'matricula' => fake()->unique()->numerify('A####'),
        'nombre' => 'Ana',
        'ap_paterno' => 'Ramírez',
        'fecha_nacimiento' => '2022-01-15',
        'estado' => 'activo',
    ]);

    $inscripcion = Inscripcion::create([
        'alumno_id' => $alumno->id,
        'ciclo_id' => $ciclo->id,
        'grupo_id' => $grupo->id,
        'fecha' => '2026-08-01',
        'activo' => true,
    ]);

    $concepto = ConceptoCobro::create([
        'nombre' => 'Colegiatura Maternal',
        'tipo' => 'colegiatura',
        'aplica_beca' => false,
        'aplica_recargo' => false,
        'activo' => true,
    ]);

    $plan = PlanPago::create([
        'ciclo_id' => $ciclo->id,
        'nivel_id' => $nivel->id,
        'nombre' => 'Plan Maternal',
        'periodicidad' => 'mensual',
        'fecha_inicio' => '2026-08-01',
        'fecha_fin' => '2027-07-31',
        'activo' => true,
    ]);

    PlanPagoConcepto::create([
        'plan_id' => $plan->id,
        'concepto_id' => $concepto->id,
        'monto' => $montoOriginal,
    ]);

    $asignacion = AsignacionPlan::create([
        'plan_id' => $plan->id,
        'alumno_id' => $alumno->id,
        'origen' => 'individual',
        'fecha_inicio' => '2026-08-01',
        'fecha_fin' => '2027-07-31',
    ]);

    $cargo = Cargo::create([
        'inscripcion_id' => $inscripcion->id,
        'concepto_id' => $concepto->id,
        'asignacion_id' => $asignacion->id,
        'monto_original' => $montoOriginal,
        'fecha_vencimiento' => '2027-01-10',
        'estado' => 'pendiente',
        'periodo' => '2027-01',
    ]);

    DescuentoCargo::create([
        'cargo_id' => $cargo->id,
        'tipo' => 'monto_fijo',
        'valor' => $montoCondonacion,
        'monto_aplicado' => $montoCondonacion,
        'motivo' => 'Condonación #4: DESCUENTO POR CICLO',
        'autorizado_por' => $admin->id,
        'creado_por' => $admin->id,
    ]);

    return compact('admin', 'alumno', 'ciclo', 'cargo', 'inscripcion');
}

test('un abono sobre un cargo con condonacion de monto fijo no debe marcarlo pagado antes de tiempo', function () {
    // Reproduce el caso real: Cargo #6 "Colegiatura Enero", monto_original=$6,400,
    // condonación fija de $3,200 (DESCUENTO POR CICLO). El cajero abona $1,200 en
    // efectivo, tal como el JS lo manda: monto_abonado = efectivo + condonación.
    $ctx = crearContextoCobroConCondonacion(6400, 3200);

    $this->actingAs($ctx['admin'])
        ->post(route('cobros.registrar'), [
            'alumno_id' => $ctx['alumno']->id,
            'forma_pago' => 'efectivo',
            'fecha_pago' => '2027-01-05',
            'items' => [[
                'tipo' => 'cargo',
                'cargo_id' => $ctx['cargo']->id,
                'monto_abonado' => 1200 + 3200, // efectivo (1200) + condonación (3200)
                'descuento_beca' => 0,
                'descuento_pronto_pago' => 0,
                'descuento_otros' => 3200,
                'recargo' => 0,
            ]],
        ])->assertRedirect();

    $ctx['cargo']->refresh();

    // Antes del fix esto se marcaba 'pagado' porque la condonación se contaba
    // dos veces (una vez dentro de monto_abonado y otra vez por separado).
    expect($ctx['cargo']->estado)->toBe('parcial');

    $vista = $this->actingAs($ctx['admin'])->get(route('cobros.alumno', $ctx['alumno']->id));
    $cargoVista = $vista->viewData('cargos')->first();

    expect((float) $cargoVista->pendiente)->toBe(2000.0);
});

test('la condonacion de monto fijo no se debe volver a aplicar en un segundo abono', function () {
    $ctx = crearContextoCobroConCondonacion(6400, 3200);
    $admin = $ctx['admin'];
    $alumno = $ctx['alumno'];
    $cargo = $ctx['cargo'];

    // Primer abono: $1,200 en efectivo (igual que el reporte real).
    $this->actingAs($admin)->post(route('cobros.registrar'), [
        'alumno_id' => $alumno->id,
        'forma_pago' => 'efectivo',
        'fecha_pago' => '2027-01-05',
        'items' => [[
            'tipo' => 'cargo',
            'cargo_id' => $cargo->id,
            'monto_abonado' => 1200 + 3200,
            'descuento_beca' => 0,
            'descuento_pronto_pago' => 0,
            'descuento_otros' => 3200,
            'recargo' => 0,
        ]],
    ])->assertRedirect();

    $cargo->refresh();
    expect($cargo->estado)->toBe('parcial');

    // Segunda visita: la condonación ya se acreditó por completo en el primer
    // abono, así que ya NO debe volver a ofrecerse — el saldo pendiente real
    // ($2,000) debe cubrirse solo con efectivo.
    $vista2 = $this->actingAs($admin)->get(route('cobros.alumno', $alumno->id));
    $cargoVista2 = $vista2->viewData('cargos')->first();

    expect((float) $cargoVista2->pendiente)->toBe(2000.0)
        ->and((float) $cargoVista2->descuento_condonacion_calc)->toBe(0.0)
        ->and((float) $cargoVista2->monto_a_pagar_hoy)->toBe(2000.0);

    // El cajero liquida el resto tal cual lo sugiere la pantalla: $2,000 en efectivo.
    $this->actingAs($admin)->post(route('cobros.registrar'), [
        'alumno_id' => $alumno->id,
        'forma_pago' => 'efectivo',
        'fecha_pago' => '2027-01-06',
        'items' => [[
            'tipo' => 'cargo',
            'cargo_id' => $cargo->id,
            'monto_abonado' => 2000,
            'descuento_beca' => 0,
            'descuento_pronto_pago' => 0,
            'descuento_otros' => 0,
            'recargo' => 0,
        ]],
    ])->assertRedirect();

    $cargo->refresh();
    expect($cargo->estado)->toBe('pagado');
});
