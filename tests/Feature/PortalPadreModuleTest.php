<?php

use App\Models\Alumno;
use App\Models\Cargo;
use App\Models\CicloEscolar;
use App\Models\ConceptoCobro;
use App\Models\ContactoFamiliar;
use App\Models\Familia;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\NivelEscolar;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function crearPortalPadreContexto(): array
{
    $padre = Usuario::create([
        'nombre' => 'Padre Portal',
        'email' => fake()->unique()->safeEmail(),
        'password_hash' => bcrypt('password'),
        'rol' => 'padre',
        'activo' => true,
    ]);

    $familia = Familia::create([
        'apellido_familia' => 'Lopez',
        'activo' => true,
    ]);

    ContactoFamiliar::create([
        'familia_id' => $familia->id,
        'usuario_id' => $padre->id,
        'tiene_acceso_portal' => true,
        'nombre' => 'Tutor',
        'ap_paterno' => 'Lopez',
        'email' => $padre->email,
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
        'familia_id' => $familia->id,
        'matricula' => fake()->unique()->numerify('A###'),
        'nombre' => 'Juan',
        'ap_paterno' => 'Lopez',
        'fecha_nacimiento' => '2017-05-10',
        'estado' => 'activo',
    ]);

    $inscripcion = Inscripcion::create([
        'alumno_id' => $alumno->id,
        'ciclo_id' => $ciclo->id,
        'grupo_id' => $grupo->id,
        'fecha' => '2026-08-15',
        'activo' => true,
    ]);

    $concepto = ConceptoCobro::create([
        'nombre' => 'Colegiatura',
        'tipo' => 'colegiatura',
        'activo' => true,
    ]);

    Cargo::create([
        'inscripcion_id' => $inscripcion->id,
        'concepto_id' => $concepto->id,
        'monto_original' => 1500,
        'fecha_vencimiento' => now()->addDays(10)->toDateString(),
        'estado' => 'pendiente',
        'periodo' => '2026-09',
    ]);

    return compact('alumno', 'padre');
}

test('padre consulta dashboard e informacion de sus hijos', function () {
    $contexto = crearPortalPadreContexto();

    $this->actingAs($contexto['padre'])
        ->get(route('portal.dashboard'))
        ->assertSuccessful()
        ->assertSee('Portal de padres')
        ->assertSee('Juan Lopez');

    $this->actingAs($contexto['padre'])
        ->get(route('portal.hijos'))
        ->assertSuccessful()
        ->assertSee('Matricula')
        ->assertSee('Grupo actual');

    $this->actingAs($contexto['padre'])
        ->get(route('portal.estado-cuenta', $contexto['alumno']->id))
        ->assertSuccessful()
        ->assertSee('Colegiatura')
        ->assertSee('$1,500.00');
});

test('padre no puede consultar alumnos de otra familia', function () {
    $contexto = crearPortalPadreContexto();

    $otraFamilia = Familia::create([
        'apellido_familia' => 'Garcia',
        'activo' => true,
    ]);

    $otroAlumno = Alumno::create([
        'familia_id' => $otraFamilia->id,
        'matricula' => fake()->unique()->numerify('B###'),
        'nombre' => 'Ana',
        'ap_paterno' => 'Garcia',
        'fecha_nacimiento' => '2016-02-10',
        'estado' => 'activo',
    ]);

    $this->actingAs($contexto['padre'])
        ->get(route('portal.estado-cuenta', $otroAlumno->id))
        ->assertForbidden();
});
