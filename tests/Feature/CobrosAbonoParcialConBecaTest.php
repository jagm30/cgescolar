<?php

use App\Models\Alumno;
use App\Models\AsignacionPlan;
use App\Models\BecaAlumno;
use App\Models\Cargo;
use App\Models\CatalogoBeca;
use App\Models\CicloEscolar;
use App\Models\ConceptoCobro;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\NivelEscolar;
use App\Models\PlanPago;
use App\Models\PlanPagoConcepto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function crearContextoCobroConBeca(float $porcentajeBeca): array
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
        'nombre' => 'Primaria',
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
        'nombre' => 'Luis',
        'ap_paterno' => 'García',
        'fecha_nacimiento' => '2016-01-15',
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
        'nombre' => 'Colegiatura',
        'tipo' => 'colegiatura',
        'aplica_beca' => true,
        'aplica_recargo' => false,
        'activo' => true,
    ]);

    $plan = PlanPago::create([
        'ciclo_id' => $ciclo->id,
        'nivel_id' => $nivel->id,
        'nombre' => 'Plan Primaria',
        'periodicidad' => 'mensual',
        'fecha_inicio' => '2026-08-01',
        'fecha_fin' => '2026-08-31',
        'activo' => true,
    ]);

    PlanPagoConcepto::create([
        'plan_id' => $plan->id,
        'concepto_id' => $concepto->id,
        'monto' => 1000,
    ]);

    $asignacion = AsignacionPlan::create([
        'plan_id' => $plan->id,
        'alumno_id' => $alumno->id,
        'origen' => 'individual',
        'fecha_inicio' => '2026-08-01',
        'fecha_fin' => '2026-08-31',
    ]);

    $catalogoBeca = CatalogoBeca::create([
        'nombre' => 'Beca hermanos',
        'tipo' => 'porcentaje',
        'valor' => $porcentajeBeca,
        'activo' => true,
    ]);

    BecaAlumno::create([
        'catalogo_beca_id' => $catalogoBeca->id,
        'alumno_id' => $alumno->id,
        'ciclo_id' => $ciclo->id,
        'plan_id' => $plan->id,
        'vigencia_inicio' => '2026-08-01',
        'activo' => true,
    ]);

    $cargo = Cargo::create([
        'inscripcion_id' => $inscripcion->id,
        'concepto_id' => $concepto->id,
        'asignacion_id' => $asignacion->id,
        'monto_original' => 1000,
        'fecha_vencimiento' => '2026-08-10',
        'estado' => 'pendiente',
        'periodo' => '2026-08',
    ]);

    return compact('admin', 'alumno', 'ciclo', 'cargo', 'inscripcion');
}

test('un abono parcial sobre un cargo con beca no debe marcarlo como pagado antes de tiempo', function () {
    // Cargo de $1000 con beca del 50% → lo que realmente se debe es $500.
    // El cajero abona solo $100 (un abono parcial real), sin tocar el campo
    // de descuento de beca (el JS ya no lo reescala, se manda tal cual el
    // servidor lo calculó en la carga de la página).
    $ctx = crearContextoCobroConBeca(50);

    $this->actingAs($ctx['admin'])
        ->post(route('cobros.registrar'), [
            'alumno_id' => $ctx['alumno']->id,
            'forma_pago' => 'efectivo',
            'fecha_pago' => '2026-08-05',
            'items' => [
                [
                    'tipo' => 'cargo',
                    'cargo_id' => $ctx['cargo']->id,
                    'monto_abonado' => 100,
                    'descuento_beca' => 0,
                    'descuento_pronto_pago' => 0,
                    'descuento_otros' => 0,
                    'recargo' => 0,
                ],
            ],
        ])->assertRedirect();

    $ctx['cargo']->refresh();

    expect($ctx['cargo']->estado)->toBe('parcial');

    $vista = $this->actingAs($ctx['admin'])->get(route('cobros.alumno', $ctx['alumno']->id));
    $cargosVista = $vista->viewData('cargos');

    expect($cargosVista->count())->toBe(1)
        ->and((float) $cargosVista->first()->pendiente)->toBe(900.0);
});

test('la beca no se debe volver a aplicar en cada abono parcial devolviendo el cargo a medio pagar', function () {
    // Cargo de $1000 con beca del 50%. El cajero paga el monto sugerido tal
    // cual el servidor lo calcula en dos abonos (simula dos visitas reales
    // al cajero, cobrando lo que la pantalla propone en cada una).
    $ctx = crearContextoCobroConBeca(50);
    $admin = $ctx['admin'];
    $alumno = $ctx['alumno'];
    $cargo = $ctx['cargo'];

    // Primera visita: la pantalla propone pagar $500 hoy (1000 - 50% beca).
    $vista1 = $this->actingAs($admin)->get(route('cobros.alumno', $alumno->id));
    $cargoVista1 = $vista1->viewData('cargos')->first();
    expect((float) $cargoVista1->monto_a_pagar_hoy)->toBe(500.0);

    // El cajero solo abona la mitad de lo sugerido: $250 en efectivo.
    // El JS manda monto_abonado = efectivo + descuento_beca (la base que
    // cubre el cargo, según el formato monto_final = monto_abonado - descuentos).
    $this->actingAs($admin)->post(route('cobros.registrar'), [
        'alumno_id' => $alumno->id,
        'forma_pago' => 'efectivo',
        'fecha_pago' => '2026-08-05',
        'items' => [[
            'tipo' => 'cargo',
            'cargo_id' => $cargo->id,
            'monto_abonado' => 250 + $cargoVista1->beca_descuento_calc,
            'descuento_beca' => $cargoVista1->beca_descuento_calc,
            'descuento_pronto_pago' => 0,
            'descuento_otros' => 0,
            'recargo' => 0,
        ]],
    ])->assertRedirect();

    $cargo->refresh();
    expect($cargo->estado)->toBe('parcial');

    // Segunda visita: como ya se acreditó el 100% de la beca disponible en
    // la primera visita, ahora NO debe volver a ofrecerse un 50% adicional
    // sobre el remanente — de lo contrario el cargo se liquidaría con solo
    // $500 en efectivo cuando debería requerir $1000.
    $vista2 = $this->actingAs($admin)->get(route('cobros.alumno', $alumno->id));
    $cargoVista2 = $vista2->viewData('cargos')->first();

    expect((float) $cargoVista2->pendiente)->toBe(250.0)
        ->and((float) $cargoVista2->beca_descuento_calc)->toBe(0.0)
        ->and((float) $cargoVista2->monto_a_pagar_hoy)->toBe(250.0);

    // El cajero liquida el resto tal cual lo sugiere la pantalla.
    $this->actingAs($admin)->post(route('cobros.registrar'), [
        'alumno_id' => $alumno->id,
        'forma_pago' => 'efectivo',
        'fecha_pago' => '2026-08-06',
        'items' => [[
            'tipo' => 'cargo',
            'cargo_id' => $cargo->id,
            'monto_abonado' => 250,
            'descuento_beca' => 0,
            'descuento_pronto_pago' => 0,
            'descuento_otros' => 0,
            'recargo' => 0,
        ]],
    ])->assertRedirect();

    $cargo->refresh();
    expect($cargo->estado)->toBe('pagado');
});

test('el estado de cuenta del alumno no debe mostrar 0 por pagar cuando ya se uso toda la beca disponible', function () {
    // Reproduce el bug de Cargo::monto_cubierto: sumaba descuento_beca encima de
    // monto_abonado (que ya lo incluye), haciendo que el estado de cuenta mostrara
    // el cargo como cubierto/sin saldo pendiente aunque solo se hubiera abonado
    // una parte en efectivo.
    $ctx = crearContextoCobroConBeca(50);
    $admin = $ctx['admin'];
    $alumno = $ctx['alumno'];
    $cargo = $ctx['cargo'];

    $this->actingAs($admin)->post(route('cobros.registrar'), [
        'alumno_id' => $alumno->id,
        'forma_pago' => 'efectivo',
        'fecha_pago' => '2026-08-05',
        'items' => [[
            'tipo' => 'cargo',
            'cargo_id' => $cargo->id,
            'monto_abonado' => 100 + 500, // efectivo (100) + beca (500, 50% de 1000)
            'descuento_beca' => 500,
            'descuento_pronto_pago' => 0,
            'descuento_otros' => 0,
            'recargo' => 0,
        ]],
    ])->assertRedirect();

    $cargo->refresh();
    expect($cargo->estado)->toBe('parcial');

    $vista = $this->actingAs($admin)->get(route('alumnos.estado-cuenta', $alumno->id));
    $vista->assertOk();

    $resumen = $vista->viewData('resumen');

    // Con $500 de beca ya acreditados y $100 en efectivo, restan $400 reales
    // por cobrar — no $0.
    expect($resumen['saldo_pendiente'])->toBe(400.0)
        ->and($resumen['total_a_pagar_hoy'])->toBe(400.0);
});
