<?php

use App\Models\Cfdi;
use App\Models\Pago;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function crearUsuarioCfdi(string $rol): Usuario
{
    return Usuario::create([
        'nombre'        => ucfirst($rol),
        'email'         => fake()->unique()->safeEmail(),
        'password_hash' => bcrypt('password'),
        'rol'           => $rol,
        'activo'        => true,
    ]);
}

// ── Acceso ────────────────────────────────────────────────────────────────────

test('administrador puede acceder al índice de facturas', function () {
    $this->actingAs(crearUsuarioCfdi('administrador'))
        ->get(route('facturas.index'))
        ->assertOk();
});

test('caja puede acceder al índice de facturas', function () {
    $this->actingAs(crearUsuarioCfdi('caja'))
        ->get(route('facturas.index'))
        ->assertOk();
});

test('recepcion no puede acceder al índice de facturas', function () {
    $this->actingAs(crearUsuarioCfdi('recepcion'))
        ->get(route('facturas.index'))
        ->assertForbidden();
});

test('usuario no autenticado es redirigido al login desde facturas', function () {
    $this->get(route('facturas.index'))->assertRedirect(route('login'));
});

// ── Listado ───────────────────────────────────────────────────────────────────


test('el índice de facturas expone la variable cfdis aunque no haya registros', function () {
    $this->actingAs(crearUsuarioCfdi('administrador'))
        ->get(route('facturas.index'))
        ->assertOk()
        ->assertViewHas('cfdis');
});
