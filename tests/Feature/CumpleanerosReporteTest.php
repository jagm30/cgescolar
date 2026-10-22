<?php

use App\Models\Alumno;
use App\Models\AlumnoContacto;
use App\Models\CicloEscolar;
use App\Models\ContactoFamiliar;
use App\Models\Familia;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\NivelEscolar;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function crearContextoCumpleaneros(): array
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

    $grupos = collect([3, 1])->mapWithKeys(fn (int $numero) => [
        $numero => Grupo::create([
            'ciclo_id' => $ciclo->id,
            'grado_id' => Grado::create(['nivel_id' => $nivel->id, 'numero' => $numero])->id,
            'nombre' => 'A',
            'activo' => true,
        ]),
    ]);

    return ['admin' => $admin, 'ciclo' => $ciclo, 'grupos' => $grupos];
}

function inscribirAlumnoConContactos(Familia $familia, CicloEscolar $ciclo, Grupo $grupo, array $contactos): Alumno
{
    $alumno = Alumno::create([
        'familia_id' => $familia->id,
        'matricula' => fake()->unique()->numerify('A####'),
        'nombre' => fake()->firstName(),
        'ap_paterno' => $familia->apellido_familia,
        'fecha_nacimiento' => '2017-05-10',
        'estado' => 'activo',
    ]);

    Inscripcion::create([
        'alumno_id' => $alumno->id,
        'ciclo_id' => $ciclo->id,
        'grupo_id' => $grupo->id,
        'fecha' => '2026-08-15',
        'activo' => true,
    ]);

    foreach ($contactos as $parentesco => $contacto) {
        AlumnoContacto::create([
            'alumno_id' => $alumno->id,
            'contacto_id' => $contacto->id,
            'parentesco' => $parentesco,
            'tipo' => 'tutor',
            'activo' => true,
        ]);
    }

    return $alumno;
}

function crearContacto(Familia $familia, string $nombre, ?string $fechaNacimiento, bool $vive = true): ContactoFamiliar
{
    return ContactoFamiliar::create([
        'familia_id' => $familia->id,
        'nombre' => $nombre,
        'ap_paterno' => $familia->apellido_familia,
        'fecha_nacimiento' => $fechaNacimiento,
        'vive' => $vive,
    ]);
}

test('muestra los cumpleañeros del mes agrupados por grupo en orden de grado', function () {
    ['admin' => $admin, 'ciclo' => $ciclo, 'grupos' => $grupos] = crearContextoCumpleaneros();

    $familiaA = Familia::create(['apellido_familia' => 'Lopez', 'activo' => true]);
    $familiaB = Familia::create(['apellido_familia' => 'Ruiz', 'activo' => true]);

    inscribirAlumnoConContactos($familiaA, $ciclo, $grupos[3], [
        'padre' => crearContacto($familiaA, 'Mario', '1985-03-20'),
        'madre' => crearContacto($familiaA, 'Laura', '1987-07-02'),
    ]);
    inscribirAlumnoConContactos($familiaB, $ciclo, $grupos[1], [
        'madre' => crearContacto($familiaB, 'Sofia', '1990-03-05'),
        'padre' => crearContacto($familiaB, 'Pedro', '1980-03-11', vive: false),
        'abuelo' => crearContacto($familiaB, 'Ramon', null),
    ]);

    $respuesta = $this->actingAs($admin)->get(route('familias.cumpleaneros', ['mes' => 3]));

    $respuesta->assertOk()
        ->assertSeeInOrder(['1° A', 'Sofia Ruiz', '3° A', 'Mario Lopez'])
        ->assertDontSee('Laura Lopez')
        ->assertDontSee('Pedro Ruiz')
        ->assertDontSee('Ramon Ruiz');
});

test('un contacto con dos hijos en el mismo grupo aparece una sola vez', function () {
    ['admin' => $admin, 'ciclo' => $ciclo, 'grupos' => $grupos] = crearContextoCumpleaneros();

    $familia = Familia::create(['apellido_familia' => 'Diaz', 'activo' => true]);
    $mama = crearContacto($familia, 'Elena', '1988-05-14');

    inscribirAlumnoConContactos($familia, $ciclo, $grupos[1], ['madre' => $mama]);
    inscribirAlumnoConContactos($familia, $ciclo, $grupos[1], ['madre' => $mama]);

    $html = $this->actingAs($admin)
        ->get(route('familias.cumpleaneros', ['mes' => 5]))
        ->assertOk()
        ->getContent();

    expect(substr_count($html, 'Elena Diaz'))->toBe(1);
});

test('exporta el reporte de cumpleañeros a PDF', function () {
    ['admin' => $admin, 'ciclo' => $ciclo, 'grupos' => $grupos] = crearContextoCumpleaneros();

    $familia = Familia::create(['apellido_familia' => 'Lopez', 'activo' => true]);
    inscribirAlumnoConContactos($familia, $ciclo, $grupos[1], [
        'padre' => crearContacto($familia, 'Mario', '1985-03-20'),
    ]);

    $this->actingAs($admin)
        ->get(route('familias.cumpleaneros.pdf', ['mes' => 3]))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

test('rechaza un mes fuera de rango', function () {
    ['admin' => $admin] = crearContextoCumpleaneros();

    $this->actingAs($admin)
        ->get(route('familias.cumpleaneros', ['mes' => 13]))
        ->assertSessionHasErrors('mes');
});
