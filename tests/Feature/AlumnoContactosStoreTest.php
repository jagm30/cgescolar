<?php

use App\Models\AlumnoContacto;
use App\Models\CicloEscolar;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\NivelEscolar;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function crearContextoInscripcion(): array
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

    return compact('admin', 'ciclo', 'nivel', 'grupo');
}

test('guarda dos contactos familiares aunque compartan el mismo telefono', function () {
    ['admin' => $admin, 'ciclo' => $ciclo, 'nivel' => $nivel, 'grupo' => $grupo] = crearContextoInscripcion();

    $telefonoCompartido = '5512345678';

    $respuesta = $this->actingAs($admin)->post(route('alumnos.store'), [
        'nombre' => 'Luis',
        'ap_paterno' => 'Hernandez',
        'fecha_nacimiento' => '2016-01-01',
        'fecha_inscripcion' => now()->format('Y-m-d'),
        'ciclo_id' => $ciclo->id,
        'nivel_id' => $nivel->id,
        'grupo_id' => $grupo->id,
        'tipo_familia' => 'nueva',
        'apellido_familia' => 'Familia Hernandez',
        'contactos' => [
            [
                'nombre' => 'Juan',
                'ap_paterno' => 'Hernandez',
                'telefono_celular' => $telefonoCompartido,
                'parentesco' => 'padre',
                'tipo' => 'padre',
                'orden' => 1,
                'autorizado_recoger' => '1',
                'es_responsable_pago' => '1',
                'tiene_acceso_portal' => '1',
            ],
            [
                'nombre' => 'Maria',
                'ap_paterno' => 'Lopez',
                'telefono_celular' => $telefonoCompartido,
                'parentesco' => 'madre',
                'tipo' => 'madre',
                'orden' => 2,
                'autorizado_recoger' => '1',
                'es_responsable_pago' => '0',
                'tiene_acceso_portal' => '0',
            ],
        ],
    ]);

    $respuesta->assertRedirect();

    expect(AlumnoContacto::count())->toBe(2);

    $vinculos = AlumnoContacto::with('contacto')->orderBy('orden')->get();

    expect($vinculos[0]->contacto->nombre)->toBe('Juan');
    expect((bool) $vinculos[0]->autorizado_recoger)->toBeTrue();
    expect((bool) $vinculos[0]->es_responsable_pago)->toBeTrue();
    expect((bool) $vinculos[0]->tiene_acceso_portal)->toBeTrue();

    expect($vinculos[1]->contacto->nombre)->toBe('Maria');
    expect((bool) $vinculos[1]->autorizado_recoger)->toBeTrue();
    expect((bool) $vinculos[1]->es_responsable_pago)->toBeFalse();
    expect((bool) $vinculos[1]->tiene_acceso_portal)->toBeFalse();
});
