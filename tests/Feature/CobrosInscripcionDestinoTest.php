<?php

use App\Enums\TipoInscripcion;
use App\Models\Alumno;
use App\Models\CicloEscolar;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\NivelEscolar;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Alumno con inscripción regular en 2026-2027 (Primaria 3° B) y una anticipada
 * activa, sin grupo, en 2025-2026 creada después (id más alto).
 */
function crearContextoInscripcionDestino(): array
{
    $cajero = Usuario::create([
        'nombre' => 'Cajero',
        'email' => fake()->unique()->safeEmail(),
        'password_hash' => bcrypt('password'),
        'rol' => 'administrador',
        'activo' => true,
    ]);

    $ciclo = CicloEscolar::create(['nombre' => '2026-2027', 'fecha_inicio' => '2026-08-01', 'fecha_fin' => '2027-07-31', 'estado' => 'activo']);
    $cicloAnterior = CicloEscolar::create(['nombre' => '2025-2026', 'fecha_inicio' => '2025-08-01', 'fecha_fin' => '2026-07-31', 'estado' => 'cerrado']);

    $nivel = NivelEscolar::create(['nombre' => 'Primaria', 'revoe' => fake()->unique()->numerify('REV####'), 'orden' => 1, 'activo' => true]);
    $grado = Grado::create(['nivel_id' => $nivel->id, 'numero' => 3]);
    $grupo = Grupo::create(['ciclo_id' => $ciclo->id, 'grado_id' => $grado->id, 'nombre' => 'B', 'activo' => true]);

    $alumno = Alumno::create([
        'matricula' => fake()->unique()->numerify('A####'),
        'nombre' => 'María',
        'ap_paterno' => 'López',
        'fecha_nacimiento' => '2015-05-10',
        'estado' => 'activo',
    ]);

    $regular = Inscripcion::create([
        'alumno_id' => $alumno->id,
        'ciclo_id' => $ciclo->id,
        'grupo_id' => $grupo->id,
        'fecha' => '2026-08-01',
        'activo' => true,
    ]);

    $anticipada = Inscripcion::create([
        'alumno_id' => $alumno->id,
        'ciclo_id' => $cicloAnterior->id,
        'grupo_id' => null,
        'fecha' => '2026-06-01',
        'activo' => true,
        'tipo' => TipoInscripcion::Anticipada,
    ]);

    $cajero->update(['ciclo_seleccionado_id' => $ciclo->id]);

    return ['cajero' => $cajero, 'alumno' => $alumno, 'regular' => $regular, 'anticipada' => $anticipada];
}

test('los conceptos nuevos se cargan a la inscripción del ciclo seleccionado', function () {
    ['cajero' => $cajero, 'alumno' => $alumno, 'regular' => $regular] = crearContextoInscripcionDestino();

    $this->actingAs($cajero)
        ->get(route('cobros.alumno', $alumno->id))
        ->assertOk()
        ->assertViewHas('inscripcionParaCobro', fn (Inscripcion $i) => $i->id === $regular->id)
        ->assertSee('Los conceptos nuevos se registrarán en:')
        ->assertSee('2026-2027 · Primaria 3° B');
});

test('sin inscripción activa en el ciclo seleccionado avisa y usa la activa más reciente', function () {
    ['cajero' => $cajero, 'alumno' => $alumno, 'regular' => $regular, 'anticipada' => $anticipada] = crearContextoInscripcionDestino();
    $regular->update(['activo' => false]);

    $this->actingAs($cajero)
        ->get(route('cobros.alumno', $alumno->id))
        ->assertOk()
        ->assertViewHas('inscripcionParaCobro', fn (Inscripcion $i) => $i->id === $anticipada->id)
        ->assertSee('Sin inscripción activa en el ciclo seleccionado (2026-2027).')
        ->assertSee('2025-2026 · Sin grupo');
});
