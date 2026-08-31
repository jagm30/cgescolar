<?php

use App\Http\Controllers\Educativo\BoletaController;
use App\Http\Controllers\Educativo\CapturaCalificacionController;
use App\Http\Controllers\Educativo\DashboardEducativoController;
use App\Http\Controllers\Educativo\Sicap\AsignacionDocenteController;
use App\Http\Controllers\Educativo\Sicap\CampoFormativoController;
use App\Http\Controllers\Educativo\Sicap\EscalaEvaluacionController;
use App\Http\Controllers\Educativo\Sicap\MateriaController;
use App\Http\Controllers\Educativo\Sicap\PeriodoEvaluativoController;
use App\Http\Controllers\Educativo\Sicap\PlanEstudiosController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Módulo Educativo — Calificaciones
|--------------------------------------------------------------------------
| Prefijo aplicado en web.php: /educativo
| Nombre base aplicado en web.php: educativo.
|
| Acceso: administrador, docente
|--------------------------------------------------------------------------
*/

Route::get('/', [DashboardEducativoController::class, 'index'])->name('dashboard');

// =======================================================
// Boleta de calificaciones (solo administrador por ahora)
// =======================================================
Route::middleware('rol:administrador')->prefix('boleta')->name('boleta.')->group(function () {
    Route::get('/', [BoletaController::class, 'index'])->name('index');
    Route::get('/{alumno}', [BoletaController::class, 'show'])->name('show');
    Route::get('/{alumno}/pdf', [BoletaController::class, 'pdf'])->name('pdf');
});

// =======================================================
// Captura de calificaciones (administrador + docente)
// =======================================================
Route::prefix('captura')->name('captura.')->group(function () {
    Route::get('/', [CapturaCalificacionController::class, 'index'])->name('index');
    Route::get('/{asignacion}', [CapturaCalificacionController::class, 'show'])->name('show');
    Route::post('/{asignacion}', [CapturaCalificacionController::class, 'store'])->name('store');
});

// =======================================================
// SICAP — Catálogos educativos (solo administrador)
// =======================================================
Route::middleware('rol:administrador')
    ->prefix('sicap')
    ->name('sicap.')
    ->group(function () {

        // ── Escalas de evaluación ────────────────────────
        Route::resource('escalas', EscalaEvaluacionController::class);

        // Criterios de una escala (gestionados inline via AJAX)
        Route::post('escalas/{escala}/criterios', [EscalaEvaluacionController::class, 'storeCriterio'])
            ->name('escalas.criterios.store');
        Route::put('escalas/{escala}/criterios/{criterio}', [EscalaEvaluacionController::class, 'updateCriterio'])
            ->name('escalas.criterios.update');
        Route::delete('escalas/{escala}/criterios/{criterio}', [EscalaEvaluacionController::class, 'destroyCriterio'])
            ->name('escalas.criterios.destroy');

        // ── Planes de estudio ────────────────────────────
        // ->parameters fuerza {plan} en lugar del {plane} que genera Laravel por defecto
        Route::resource('planes', PlanEstudiosController::class)
            ->parameters(['planes' => 'plan']);

        // Materias de un plan (AJAX desde planes/show)
        Route::post('planes/{plan}/materias/reordenar', [MateriaController::class, 'reordenar'])
            ->name('planes.materias.reordenar');
        Route::post('planes/{plan}/materias', [MateriaController::class, 'store'])
            ->name('planes.materias.store');
        Route::put('planes/{plan}/materias/{materia}', [MateriaController::class, 'update'])
            ->name('planes.materias.update');
        Route::delete('planes/{plan}/materias/{materia}', [MateriaController::class, 'destroy'])
            ->name('planes.materias.destroy');

        // Campos formativos de un plan (AJAX desde planes/show — Preescolar)
        Route::post('planes/{plan}/campos', [CampoFormativoController::class, 'store'])
            ->name('planes.campos.store');
        Route::put('planes/{plan}/campos/{campo}', [CampoFormativoController::class, 'update'])
            ->name('planes.campos.update');
        Route::delete('planes/{plan}/campos/{campo}', [CampoFormativoController::class, 'destroy'])
            ->name('planes.campos.destroy');

        // ── Períodos evaluativos ─────────────────────────
        Route::post('periodos/generar', [PeriodoEvaluativoController::class, 'generar'])
            ->name('periodos.generar');
        Route::patch('periodos/{periodo}/abrir', [PeriodoEvaluativoController::class, 'abrir'])
            ->name('periodos.abrir');
        Route::patch('periodos/{periodo}/cerrar', [PeriodoEvaluativoController::class, 'cerrar'])
            ->name('periodos.cerrar');
        Route::resource('periodos', PeriodoEvaluativoController::class)
            ->parameters(['periodos' => 'periodo']);

        // ── Asignaciones docente-materia ─────────────────
        Route::patch('asignaciones/{asignacion}/toggle', [AsignacionDocenteController::class, 'toggle'])
            ->name('asignaciones.toggle');
        Route::resource('asignaciones', AsignacionDocenteController::class)
            ->parameters(['asignaciones' => 'asignacion']);
    });
