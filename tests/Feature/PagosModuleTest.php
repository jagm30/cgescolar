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
use App\Models\Pago;
use App\Models\PlanPago;
use App\Models\PlanPagoConcepto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function crearContextoPago(): array
{
    $admin = Usuario::create([
        'nombre'        => 'Admin Caja',
        'email'         => fake()->unique()->safeEmail(),
        'password_hash' => bcrypt('password'),
        'rol'           => 'administrador',
        'activo'        => true,
    ]);

    $ciclo = CicloEscolar::create([
        'nombre'       => '2026-2027',
        'fecha_inicio' => '2026-08-01',
        'fecha_fin'    => '2027-07-31',
        'estado'       => 'activo',
    ]);

    $nivel = NivelEscolar::create([
        'nombre'  => 'Primaria',
        'revoe'   => fake()->unique()->numerify('REV####'),
        'orden'   => 1,
        'activo'  => true,
    ]);

    $grado = Grado::create(['nivel_id' => $nivel->id, 'numero' => 1]);

    $grupo = Grupo::create([
        'ciclo_id' => $ciclo->id,
        'grado_id' => $grado->id,
        'nombre'   => 'A',
        'activo'   => true,
    ]);

    $alumno = Alumno::create([
        'matricula'        => fake()->unique()->numerify('A####'),
        'nombre'           => 'Luis',
        'ap_paterno'       => 'García',
        'fecha_nacimiento' => '2016-01-15',
        'estado'           => 'activo',
    ]);

    $inscripcion = Inscripcion::create([
        'alumno_id' => $alumno->id,
        'ciclo_id'  => $ciclo->id,
        'grupo_id'  => $grupo->id,
        'fecha'     => '2026-08-01',
        'activo'    => true,
    ]);

    $concepto = ConceptoCobro::create([
        'nombre'         => 'Colegiatura',
        'tipo'           => 'colegiatura',
        'aplica_beca'    => false,
        'aplica_recargo' => false,
        'activo'         => true,
    ]);

    $plan = PlanPago::create([
        'ciclo_id'     => $ciclo->id,
        'nivel_id'     => $nivel->id,
        'nombre'       => 'Plan Primaria',
        'periodicidad' => 'mensual',
        'fecha_inicio' => '2026-08-01',
        'fecha_fin'    => '2026-08-31',
        'activo'       => true,
    ]);

    PlanPagoConcepto::create([
        'plan_id'     => $plan->id,
        'concepto_id' => $concepto->id,
        'monto'       => 1500,
    ]);

    $asignacion = AsignacionPlan::create([
        'plan_id'      => $plan->id,
        'alumno_id'    => $alumno->id,
        'origen'       => 'individual',
        'fecha_inicio' => '2026-08-01',
        'fecha_fin'    => '2026-08-31',
    ]);

    $cargo = Cargo::create([
        'inscripcion_id'    => $inscripcion->id,
        'concepto_id'       => $concepto->id,
        'asignacion_id'     => $asignacion->id,
        'monto_original'    => 1500,
        'fecha_vencimiento' => '2026-08-10',
        'estado'            => 'pendiente',
        'periodo'           => '2026-08',
    ]);

    return compact('admin', 'alumno', 'ciclo', 'cargo', 'inscripcion');
}

// ── Acceso ────────────────────────────────────────────────────────────────────

test('administrador puede acceder al índice de pagos', function () {
    $admin = Usuario::create([
        'nombre'        => 'Admin',
        'email'         => fake()->unique()->safeEmail(),
        'password_hash' => bcrypt('password'),
        'rol'           => 'administrador',
        'activo'        => true,
    ]);

    $this->actingAs($admin)->get(route('pagos.index'))->assertOk();
});

test('usuario de caja puede acceder al índice de pagos', function () {
    $caja = Usuario::create([
        'nombre'        => 'Cajero',
        'email'         => fake()->unique()->safeEmail(),
        'password_hash' => bcrypt('password'),
        'rol'           => 'caja',
        'activo'        => true,
    ]);

    $this->actingAs($caja)->get(route('pagos.index'))->assertOk();
});

test('recepcion no puede acceder al índice de pagos', function () {
    $recepcion = Usuario::create([
        'nombre'        => 'Recepción',
        'email'         => fake()->unique()->safeEmail(),
        'password_hash' => bcrypt('password'),
        'rol'           => 'recepcion',
        'activo'        => true,
    ]);

    $this->actingAs($recepcion)->get(route('pagos.index'))->assertForbidden();
});

test('usuario no autenticado es redirigido al login', function () {
    $this->get(route('pagos.index'))->assertRedirect(route('login'));
});

// ── Store ─────────────────────────────────────────────────────────────────────

test('administrador puede registrar un pago sobre un cargo pendiente', function () {
    $ctx = crearContextoPago();

    $response = $this->actingAs($ctx['admin'])
        ->post(route('pagos.store'), [
            'forma_pago' => 'efectivo',
            'fecha_pago' => '2026-08-05',
            'detalles'   => [
                ['cargo_id' => $ctx['cargo']->id, 'monto' => 1500],
            ],
        ]);

    $response->assertRedirect();

    expect(Pago::count())->toBe(1);

    $this->assertDatabaseHas('cargo', [
        'id'     => $ctx['cargo']->id,
        'estado' => 'pagado',
    ]);
});
