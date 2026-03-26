<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias para usar ->middleware('rol:administrador') en rutas
    $middleware->alias([
        'rol'               => \App\Http\Middleware\CheckRol::class,
        'force.json.on.ajax'=> \App\Http\Middleware\ForceJsonOnAjax::class,
    ]);

    // Agregar ForceJsonOnAjax al grupo 'web' para que aplique en todas las rutas
    $middleware->appendToGroup('web', \App\Http\Middleware\ForceJsonOnAjax::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
