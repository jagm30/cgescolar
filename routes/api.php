<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\BecaController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\CicloEscolarController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PlanPagoController;
use App\Http\Controllers\PortalPadreController;
use App\Http\Controllers\ProspectoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\GradoController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\NivelEscolarController;
use Illuminate\Support\Facades\Route;

// ── Autenticación (pública) ──────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login',  [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('me',      [AuthController::class, 'me'])->middleware('auth:sanctum');
});

// ── Rutas protegidas ─────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {    

    //web vistas
    Route::resource('ciclos', CicloEscolarController::class)->only(['index', 'show', 'store', 'update']);
    Route::resource('niveles', NivelEscolarController::class)->only(['index', 'show', 'store', 'update']);
    Route::resource('grados', GradoController::class)->only(['index', 'show', 'store', 'update']);
    Route::resource('grupos', GrupoController::class)->only(['index', 'show', 'store', 'update']);

    // ── Ciclos ───────────────────────────────────────────
    Route::get('ciclos/activo',          [CicloEscolarController::class, 'activo']);
    Route::post('ciclos/{id}/seleccionar',[CicloEscolarController::class, 'seleccionar']);
    Route::apiResource('ciclos', CicloEscolarController::class)->only(['index', 'show', 'store', 'update']);

     // ── Niveles escolares ────────────────────────────────
    Route::apiResource('niveles', NivelEscolarController::class);

    // ── Grados ───────────────────────────────────────────
    Route::apiResource('grados', GradoController::class);

    // ── Grupos ───────────────────────────────────────────
    Route::post('grupos/{id}/cambiar-alumno', [GrupoController::class, 'cambiarAlumno']);
    Route::apiResource('grupos', GrupoController::class);

    // ── Alumnos ──────────────────────────────────────────
    Route::get('alumnos/{id}/hermanos',      [AlumnoController::class, 'hermanos']);
    Route::get('alumnos/{id}/estado-cuenta', [AlumnoController::class, 'estadoCuenta']);
    Route::apiResource('alumnos', AlumnoController::class);

    // ── Planes de pago ───────────────────────────────────
    Route::post('planes/asignar',                      [PlanPagoController::class, 'asignar']);
    Route::get('planes/asignacion/{alumnoId}',         [PlanPagoController::class, 'asignacionDeAlumno']);
    Route::apiResource('planes', PlanPagoController::class);

    // ── Cargos ───────────────────────────────────────────
    Route::post('cargos/generar',    [CargoController::class, 'generar']);
    Route::get('cargos/{id}/preview',[CargoController::class, 'preview']);
    Route::apiResource('cargos', CargoController::class)->only(['index', 'show']);

    // ── Pagos ────────────────────────────────────────────
    Route::post('pagos/{id}/anular', [PagoController::class, 'anular']);
    Route::get('pagos/corte',        [PagoController::class, 'corte']);
    Route::apiResource('pagos', PagoController::class)->only(['index', 'show', 'store']);

    // ── Becas ────────────────────────────────────────────
    Route::get('becas/catalogo',     [BecaController::class, 'catalogo']);
    Route::post('becas/catalogo',    [BecaController::class, 'storeCatalogo']);
    Route::apiResource('becas', BecaController::class)->only(['index', 'store', 'destroy']);

    // ── Prospectos ───────────────────────────────────────
    Route::get('prospectos/metricas',               [ProspectoController::class, 'metricas']);
    Route::post('prospectos/{id}/etapa',            [ProspectoController::class, 'cambiarEtapa']);
    Route::post('prospectos/{id}/seguimiento',      [ProspectoController::class, 'agregarSeguimiento']);
    Route::apiResource('prospectos', ProspectoController::class)->only(['index', 'show', 'store']);

    // ── Usuarios ─────────────────────────────────────────
    Route::get('usuarios/perfil',            [UsuarioController::class, 'perfil']);
    Route::get('usuarios/pendientes-portal', [UsuarioController::class, 'pendientesPortal']);
    Route::apiResource('usuarios', UsuarioController::class);

    // ── Portal de padres ─────────────────────────────────
    Route::prefix('portal')->group(function () {
        Route::get('hijos',                              [PortalPadreController::class, 'hijos']);
        Route::get('hijos/{alumnoId}/estado-cuenta',    [PortalPadreController::class, 'estadoCuenta']);
        Route::get('hijos/{alumnoId}/pagos',            [PortalPadreController::class, 'historialPagos']);
        Route::get('razones-sociales',                  [PortalPadreController::class, 'razonesSociales']);
    });
});

// ── Niveles escolares ────────────────────────────────────
Route::apiResource('niveles', \App\Http\Controllers\NivelEscolarController::class);