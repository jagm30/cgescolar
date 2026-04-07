<?php

use App\Models\Usuario;

it('returns the correct dashboard route for each role', function (string $rol, string $routeName) {
    $usuario = new Usuario(['rol' => $rol]);

    expect($usuario->rutaDashboard())->toBe(route($routeName));
})->with([
    ['administrador', 'admin.dashboard'],
    ['caja', 'caja.dashboard'],
    ['recepcion', 'recepcion.dashboard'],
    ['padre', 'portal.dashboard'],
    ['otro', 'login'],
]);
