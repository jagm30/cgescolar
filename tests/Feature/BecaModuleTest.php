<?php

use App\Models\Alumno;
use App\Models\AsignacionPlan;
use App\Models\BecaAlumno;
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

function crearContextoBeca(): array
{
    $admin = Usuario::create([
        'nombre' => 'Admin',
        'email' => fake()->unique()->safeEmail(),
        'password_hash' => bcrypt('password'),
        'rol' => 'administrador',
        'activo' => true,
    ]);

    $ciclo = CicloEscolar::create([
        'nombre' => '2025-2026',
        'fecha_inicio' => '2025-08-01',
        'fecha_fin' => '2026-07-31',
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
        'nombre' => 'Maria',
        'ap_paterno' => 'Perez',
        'fecha_nacimiento' => '2015-05-10',
        'estado' => 'activo',
    ]);

    Inscripcion::create([
        'alumno_id' => $alumno->id,
        'ciclo_id' => $ciclo->id,
        'grupo_id' => $grupo->id,
        'fecha' => '2025-08-15',
        'activo' => true,
    ]);

    $concepto = ConceptoCobro::create([
        'nombre' => 'Colegiatura Primaria',
        'tipo' => 'colegiatura',
        'aplica_beca' => true,
        'activo' => true,
    ]);

    $plan = PlanPago::create([
        'ciclo_id' => $ciclo->id,
        'nivel_id' => $nivel->id,
        'nombre' => 'Plan Primaria',
        'periodicidad' => 'mensual',
        'fecha_inicio' => '2025-08-01',
        'fecha_fin' => '2026-06-30',
        'activo' => true,
    ]);

    PlanPagoConcepto::create([
        'plan_id' => $plan->id,
        'concepto_id' => $concepto->id,
        'monto' => 1500,
    ]);

    AsignacionPlan::create([
        'plan_id' => $plan->id,
        'alumno_id' => $alumno->id,
        'origen' => 'individual',
        'fecha_inicio' => '2025-08-01',
        'fecha_fin' => '2026-06-30',
    ]);

    return compact('admin', 'alumno', 'ciclo', 'plan');
}

test('administrador puede asignar una beca a un alumno sobre un plan de pagos', function () {
    $contexto = crearContextoBeca();

    $catalogo = CatalogoBeca::create([
        'nombre' => 'Beca del merito',
        'descripcion' => 'Descuento especial por merito academico.',
        'tipo' => 'porcentaje',
        'valor' => 20,
        'activo' => true,
    ]);

    $response = $this->actingAs($contexto['admin'])
        ->post(route('becas.store'), [
            'catalogo_beca_id' => $catalogo->id,
            'alumno_id' => $contexto['alumno']->id,
            'ciclo_id' => $contexto['ciclo']->id,
            'plan_id' => $contexto['plan']->id,
            'vigencia_inicio' => '2025-08-01',
            'vigencia_fin' => '2026-01-31',
            'motivo' => 'Beca de prueba',
        ]);

    $response->assertRedirect(route('becas.index'));

    $this->assertDatabaseHas('beca_alumno', [
        'alumno_id' => $contexto['alumno']->id,
        'catalogo_beca_id' => $catalogo->id,
        'ciclo_id' => $contexto['ciclo']->id,
        'plan_id' => $contexto['plan']->id,
        'concepto_id' => null,
        'activo' => true,
    ]);
});

test('administrador puede reemplazar la beca activa marcando deshabilitar anterior', function () {
    $contexto = crearContextoBeca();

    $becaAnterior = CatalogoBeca::create([
        'nombre' => 'Beca anterior',
        'descripcion' => 'Beca anterior.',
        'tipo' => 'monto_fijo',
        'valor' => 500,
        'activo' => true,
    ]);

    $nuevaBeca = CatalogoBeca::create([
        'nombre' => 'Beca nueva',
        'descripcion' => 'Beca de reemplazo.',
        'tipo' => 'porcentaje',
        'valor' => 10,
        'activo' => true,
    ]);

    BecaAlumno::create([
        'catalogo_beca_id' => $becaAnterior->id,
        'alumno_id' => $contexto['alumno']->id,
        'ciclo_id' => $contexto['ciclo']->id,
        'plan_id' => $contexto['plan']->id,
        'vigencia_inicio' => '2025-08-01',
        'vigencia_fin' => '2026-01-31',
        'activo' => true,
    ]);

    $response = $this->actingAs($contexto['admin'])
        ->post(route('becas.store'), [
            'catalogo_beca_id' => $nuevaBeca->id,
            'alumno_id' => $contexto['alumno']->id,
            'ciclo_id' => $contexto['ciclo']->id,
            'plan_id' => $contexto['plan']->id,
            'vigencia_inicio' => '2025-08-01',
            'vigencia_fin' => '2026-01-31',
            'motivo' => 'Reemplazo de beca',
            'deshabilitar_beca_anterior' => '1',
        ]);

    $response->assertRedirect(route('becas.index'));

    $this->assertDatabaseHas('beca_alumno', [
        'alumno_id' => $contexto['alumno']->id,
        'catalogo_beca_id' => $becaAnterior->id,
        'activo' => false,
    ]);

    $this->assertDatabaseHas('beca_alumno', [
        'alumno_id' => $contexto['alumno']->id,
        'catalogo_beca_id' => $nuevaBeca->id,
        'plan_id' => $contexto['plan']->id,
        'activo' => true,
    ]);
});
