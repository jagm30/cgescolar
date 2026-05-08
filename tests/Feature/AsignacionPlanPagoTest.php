<?php

use App\Models\Alumno;
use App\Models\AsignacionPlan;
use App\Models\Cargo;
use App\Models\CicloEscolar;
use App\Models\ConceptoCobro;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\NivelEscolar;
use App\Models\PlanPago;
use App\Models\PlanPagoConcepto;
use App\Models\PoliticaDescuento;
use App\Models\PoliticaRecargo;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function crearContextoPlanPago(): array
{
    $admin = Usuario::create([
        'nombre' => 'Admin',
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

    $grado = Grado::create([
        'nivel_id' => $nivel->id,
        'nombre' => '1',
        'numero' => 1,
    ]);

    $grupo = Grupo::create([
        'ciclo_id' => $ciclo->id,
        'grado_id' => $grado->id,
        'nombre' => 'A',
        'activo' => true,
    ]);

    $alumno = Alumno::create([
        'matricula' => fake()->unique()->numerify('A###'),
        'nombre' => 'Juan',
        'ap_paterno' => 'Pérez',
        'fecha_nacimiento' => '2016-05-10',
        'estado' => 'activo',
    ]);

    $inscripcion = Inscripcion::create([
        'alumno_id' => $alumno->id,
        'ciclo_id' => $ciclo->id,
        'grupo_id' => $grupo->id,
        'fecha' => '2026-08-15',
        'activo' => true,
    ]);

    $plan = PlanPago::create([
        'ciclo_id' => $ciclo->id,
        'nivel_id' => $nivel->id,
        'nombre' => 'Plan Primaria',
        'periodicidad' => 'mensual',
        'fecha_inicio' => '2026-08-01',
        'fecha_fin' => '2026-09-30',
        'activo' => true,
    ]);

    $concepto = ConceptoCobro::create([
        'nombre' => 'Colegiatura',
        'tipo' => 'colegiatura',
        'aplica_beca' => true,
        'aplica_recargo' => true,
        'activo' => true,
    ]);

    $planConcepto = PlanPagoConcepto::create([
        'plan_id' => $plan->id,
        'concepto_id' => $concepto->id,
        'monto' => 1500,
    ]);

    return compact(
        'admin',
        'alumno',
        'ciclo',
        'concepto',
        'grado',
        'grupo',
        'inscripcion',
        'nivel',
        'plan',
        'planConcepto'
    );
}

test('genera cargos automaticamente al asignar un plan', function () {
    $contexto = crearContextoPlanPago();

    $response = $this->actingAs($contexto['admin'])
        ->post(route('planes.asignar'), [
            'plan_id' => $contexto['plan']->id,
            'origen' => 'individual',
            'alumno_id' => $contexto['alumno']->id,
            'fecha_inicio' => '2026-08-01',
            'fecha_fin' => '2026-09-30',
            'conceptos' => [$contexto['planConcepto']->id],
        ]);

    $response->assertRedirect(route('planes.asignar.index'));

    expect(Cargo::count())->toBe(2);

    $this->assertDatabaseHas('cargo', [
        'inscripcion_id' => $contexto['inscripcion']->id,
        'concepto_id' => $contexto['concepto']->id,
        'periodo' => '2026-08',
        'monto_original' => '1500.00',
    ]);

    $this->assertDatabaseHas('cargo', [
        'inscripcion_id' => $contexto['inscripcion']->id,
        'concepto_id' => $contexto['concepto']->id,
        'periodo' => '2026-09',
        'monto_original' => '1500.00',
    ]);
});

test('permite crear un plan sin politicas de descuento ni recargo', function () {
    $contexto = crearContextoPlanPago();

    $response = $this->actingAs($contexto['admin'])
        ->post(route('planes.store'), [
            'ciclo_id' => $contexto['ciclo']->id,
            'nivel_id' => $contexto['nivel']->id,
            'nombre' => 'Plan sin politicas',
            'periodicidad' => 'mensual',
            'fecha_inicio' => '2026-08-01',
            'fecha_fin' => '2026-09-30',
            'conceptos' => [
                [
                    'concepto_id' => $contexto['concepto']->id,
                    'monto' => 1200,
                ],
            ],
            'descuentos' => [
                [
                    'nombre' => null,
                    'tipo_valor' => 'porcentaje',
                    'valor' => null,
                    'dia_limite' => null,
                ],
            ],
            'recargo' => [
                'tipo_recargo' => 'porcentaje',
            ],
        ]);

    $plan = PlanPago::where('nombre', 'Plan sin politicas')->firstOrFail();

    $response->assertRedirect(route('planes.show', $plan->id));

    expect(PoliticaDescuento::where('plan_id', $plan->id)->exists())->toBeFalse();
    expect(PoliticaRecargo::where('plan_id', $plan->id)->exists())->toBeFalse();
});

test('no permite duplicar la asignacion del mismo alcance en el ciclo', function () {
    $contexto = crearContextoPlanPago();

    $payload = [
        'plan_id' => $contexto['plan']->id,
        'origen' => 'individual',
        'alumno_id' => $contexto['alumno']->id,
        'fecha_inicio' => '2026-08-01',
        'fecha_fin' => '2026-09-30',
        'conceptos' => [$contexto['planConcepto']->id],
    ];

    $this->actingAs($contexto['admin'])->post(route('planes.asignar'), $payload);

    $response = $this->from(route('planes.asignar.form'))
        ->actingAs($contexto['admin'])
        ->post(route('planes.asignar'), $payload);

    $response->assertRedirect(route('planes.asignar.form'));
    $response->assertSessionHasErrors('plan_id');

    expect(AsignacionPlan::count())->toBe(1);
    expect(Cargo::count())->toBe(2);
});
