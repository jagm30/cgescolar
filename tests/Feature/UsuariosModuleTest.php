<?php

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function crearAdmin(): Usuario
{
    return Usuario::create([
        'nombre'        => 'Admin',
        'email'         => fake()->unique()->safeEmail(),
        'password_hash' => bcrypt('password'),
        'rol'           => 'administrador',
        'activo'        => true,
    ]);
}

// ── Acceso ────────────────────────────────────────────────────────────────────

test('administrador puede acceder al índice de usuarios', function () {
    $this->actingAs(crearAdmin())->get(route('usuarios.index'))->assertOk();
});

test('usuario de caja no puede acceder al índice de usuarios', function () {
    $caja = Usuario::create([
        'nombre'        => 'Caja',
        'email'         => fake()->unique()->safeEmail(),
        'password_hash' => bcrypt('password'),
        'rol'           => 'caja',
        'activo'        => true,
    ]);

    $this->actingAs($caja)->get(route('usuarios.index'))->assertForbidden();
});

test('usuario no autenticado es redirigido al login desde usuarios', function () {
    $this->get(route('usuarios.index'))->assertRedirect(route('login'));
});

// ── Store ─────────────────────────────────────────────────────────────────────

test('administrador puede crear un usuario interno', function () {
    $email = fake()->unique()->safeEmail();

    $response = $this->actingAs(crearAdmin())
        ->postJson(route('usuarios.store'), [
            'nombre'   => 'Nueva Recepción',
            'email'    => $email,
            'rol'      => 'recepcion',
            'password' => 'secret123',
        ]);

    $response->assertOk()->assertJsonPath('status', 'success');

    $this->assertDatabaseHas('usuario', [
        'email' => $email,
        'rol'   => 'recepcion',
        'activo' => true,
    ]);
});

test('no se puede crear un usuario con email duplicado', function () {
    $admin = crearAdmin();

    $response = $this->actingAs($admin)
        ->postJson(route('usuarios.store'), [
            'nombre'   => 'Duplicado',
            'email'    => $admin->email,
            'rol'      => 'recepcion',
            'password' => 'secret123',
        ]);

    $response->assertStatus(422)->assertJsonPath('status', 'error');

    expect(Usuario::where('email', $admin->email)->count())->toBe(1);
});

// ── Update ────────────────────────────────────────────────────────────────────

test('administrador puede cambiar el rol de un usuario', function () {
    $admin  = crearAdmin();
    $target = Usuario::create([
        'nombre'        => 'Cajero',
        'email'         => fake()->unique()->safeEmail(),
        'password_hash' => bcrypt('password'),
        'rol'           => 'caja',
        'activo'        => true,
    ]);

    $response = $this->actingAs($admin)
        ->putJson(route('usuarios.update', $target->id), [
            'nombre' => $target->nombre,
            'email'  => $target->email,
            'rol'    => 'recepcion',
        ]);

    $response->assertOk();

    $this->assertDatabaseHas('usuario', [
        'id'  => $target->id,
        'rol' => 'recepcion',
    ]);
});
