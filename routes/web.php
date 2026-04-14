<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CicloEscolarController;
use App\Http\Controllers\FamiliaController;
use App\Http\Controllers\GradoController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\NivelEscolarController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\PlanPagoController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\BecaController;
use App\Http\Controllers\ProspectoController;
use App\Http\Controllers\PortalPadreController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ConceptoCobroController;
use App\Http\Controllers\PlanPagoConceptoController;
use App\Http\Controllers\PoliticaController;
use App\Http\Controllers\CobrosController;
use App\Http\Controllers\PoliticaController;


Route::get('/tables', function () {return view('plantilla.tables');})->name('tables');
Route::get('/datatables', function () {return view('plantilla.data');})->name('datatables');
Route::get('/ui-elements', function () {return view('plantilla.uiGeneral');})->name('ui-elements');
Route::get('/forms', function () {return view('plantilla.forms');})->name('forms');
Route::get('/icons', function () {return view('plantilla.icons');})->name('icons');
Route::get('/widgets', function () {return view('plantilla.widgets');})->name('widgets');
// =======================================================
// Rutas públicas — sin autenticación
// =======================================================

Route::get('/',  [AuthController::class, 'showLogin'])->name('login');

Route::middleware('guest')->group(function () {

    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// =======================================================
// Rutas internas — administrador, caja, recepción
// =======================================================
Route::middleware(['auth', 'force.json.on.ajax'])->group(function () {


    // ── Ciclos ───────────────────────────────────────────
    // IMPORTANTE: rutas con segmento fijo ANTES del resource
    Route::post('/ciclos/{id}/seleccionar', [CicloEscolarController::class, 'seleccionar'])
        ->middleware('rol:administrador,caja,recepcion')
        ->name('ciclos.seleccionar');

    Route::resource('ciclos', CicloEscolarController::class)
        ->middleware('rol:administrador');

    // ── Niveles ──────────────────────────────────────────
    Route::resource('niveles', NivelEscolarController::class)
        ->middleware('rol:administrador');

    // ── Grados ───────────────────────────────────────────
    Route::resource('grados', GradoController::class)
        ->middleware('rol:administrador');

    // ── Grupos ───────────────────────────────────────────
    // IMPORTANTE: ruta fija ANTES del resource
    Route::post('/grupos/{id}/cambiar-alumno', [GrupoController::class, 'cambiarAlumno'])
        ->middleware('rol:administrador')
        ->name('grupos.cambiar-alumno');

    Route::resource('grupos', GrupoController::class)
        ->middleware('rol:administrador');

    // ── Alumnos ──────────────────────────────────────────
    // Rutas extra ANTES del resource
    Route::get('/alumnos/{id}/hermanos',      [AlumnoController::class, 'hermanos'])
        ->middleware('rol:administrador,recepcion')
        ->name('alumnos.hermanos');

    Route::get('/alumnos/{id}/estado-cuenta', [AlumnoController::class, 'estadoCuenta'])
        ->middleware('rol:administrador,caja,recepcion')
        ->name('alumnos.estado-cuenta');

    Route::resource('alumnos', AlumnoController::class)
        ->middleware('rol:administrador,recepcion');

    // conceptos de cobro 
    // Planes de pago
    // conceptos de cobro
    Route::resource('conceptos', ConceptoCobroController::class)
    ->middleware('rol:administrador');
    
    // ── Políticas de descuento y recargo (anidadas en plan) ──
    Route::prefix('planes/{planId}/politicas')
        ->middleware('rol:administrador')
        ->name('planes.politicas.')
        ->group(function () {
            Route::get('/',                    [PoliticaController::class, 'index'])          ->name('index');

            // Descuentos
            Route::post('descuento',           [PoliticaController::class, 'storeDescuento']) ->name('descuento.store');
            Route::put('descuento/{id}',       [PoliticaController::class, 'updateDescuento'])->name('descuento.update');
            Route::delete('descuento/{id}',    [PoliticaController::class, 'destroyDescuento'])->name('descuento.destroy');

            // Recargo
            Route::post('recargo',             [PoliticaController::class, 'storeRecargo'])   ->name('recargo.store');
            Route::put('recargo/{id}',         [PoliticaController::class, 'updateRecargo'])  ->name('recargo.update');
            Route::delete('recargo/{id}',      [PoliticaController::class, 'destroyRecargo']) ->name('recargo.destroy');
        });
    // ── Planes de pago ───────────────────────────────────
    Route::post('/planes/asignar', [PlanPagoController::class, 'asignar'])
        ->middleware('rol:administrador')
        ->name('planes.asignar');

    Route::get('/planes/asignacion/{alumnoId}', [PlanPagoController::class, 'asignacionDeAlumno'])
        ->middleware('rol:administrador,caja')
        ->name('planes.asignacion-alumno');

    Route::resource('planes', PlanPagoController::class)
        ->middleware('rol:administrador');
        
    Route::post('planes/clonar-masivo', [PlanPagoController::class, 'clonarMasivo'])->name('planes.clonar.masivo');

    // ── Conceptos y Políticas de un plan (Configuración) ──
    Route::prefix('planes/{planId}')->name('planes.')->group(function () {
        // Conceptos del plan
        Route::get('conceptos',              [PlanPagoConceptoController::class, 'index'])->name('conceptos.index');
        Route::post('conceptos',             [PlanPagoConceptoController::class, 'store'])->name('conceptos.store');
        Route::put('conceptos/{id}',         [PlanPagoConceptoController::class, 'update'])->name('conceptos.update');
        Route::delete('conceptos/{id}',      [PlanPagoConceptoController::class, 'destroy'])->name('conceptos.destroy');

        // Políticas (descuentos + recargo)
        Route::get('politicas',                  [PoliticaController::class, 'index'])->name('politicas.index');
        Route::post('politicas/descuento',       [PoliticaController::class, 'storeDescuento'])->name('politicas.descuento.store');
        Route::put('politicas/descuento/{id}',   [PoliticaController::class, 'updateDescuento'])->name('politicas.descuento.update');
        Route::delete('politicas/descuento/{id}',[PoliticaController::class, 'destroyDescuento'])->name('politicas.descuento.destroy');
        Route::post('politicas/recargo',         [PoliticaController::class, 'storeRecargo'])->name('politicas.recargo.store');
        Route::put('politicas/recargo/{id}',     [PoliticaController::class, 'updateRecargo'])->name('politicas.recargo.update');
        Route::delete('politicas/recargo/{id}',  [PoliticaController::class, 'destroyRecargo'])->name('politicas.recargo.destroy');
    })->middleware('rol:administrador');

    // ── Cargos ───────────────────────────────────────────
    Route::post('/cargos/generar',       [CargoController::class, 'generar'])
        ->middleware('rol:administrador')
        ->name('cargos.generar');

    Route::get('/cargos/{id}/preview',   [CargoController::class, 'preview'])
        ->middleware('rol:administrador,caja')
        ->name('cargos.preview');

    Route::resource('cargos', CargoController::class)
        ->only(['index', 'show'])
        ->middleware('rol:administrador,caja');

    // ── Pagos ────────────────────────────────────────────
    Route::get('/pagos/corte',           [PagoController::class, 'corte'])
        ->middleware('rol:administrador,caja')
        ->name('pagos.corte');

    Route::post('/pagos/{id}/anular',    [PagoController::class, 'anular'])
        ->middleware('rol:administrador')
        ->name('pagos.anular');

    Route::resource('pagos', PagoController::class)
        ->only(['index', 'show', 'create', 'store'])
        ->middleware('rol:administrador,caja');

    // ── Becas ────────────────────────────────────────────
    Route::get('/becas/catalogo',        [BecaController::class, 'catalogo'])
        ->middleware('rol:administrador')
        ->name('becas.catalogo');

    Route::post('/becas/catalogo',       [BecaController::class, 'storeCatalogo'])
        ->middleware('rol:administrador')
        ->name('becas.catalogo.store');

    Route::resource('becas', BecaController::class)
        ->only(['index', 'store', 'destroy'])
        ->middleware('rol:administrador');

    // ── Prospectos ───────────────────────────────────────
    Route::get('/prospectos/metricas',           [ProspectoController::class, 'metricas'])
        ->middleware('rol:administrador,recepcion')
        ->name('prospectos.metricas');

    Route::post('/prospectos/{id}/etapa',        [ProspectoController::class, 'cambiarEtapa'])
        ->middleware('rol:administrador,recepcion')
        ->name('prospectos.etapa');

    Route::post('/prospectos/{id}/seguimiento',  [ProspectoController::class, 'agregarSeguimiento'])
        ->middleware('rol:administrador,recepcion')
        ->name('prospectos.seguimiento');

    Route::resource('prospectos', ProspectoController::class)
        ->only(['index', 'show', 'create', 'store'])
        ->middleware('rol:administrador,recepcion');

    // ── Usuarios ─────────────────────────────────────────
    Route::get('/usuarios/pendientes-portal', [UsuarioController::class, 'pendientesPortal'])
        ->middleware('rol:administrador')
        ->name('usuarios.pendientes-portal');

    Route::get('/perfil', [UsuarioController::class, 'perfil'])
        ->name('usuarios.perfil');

    Route::resource('usuarios', UsuarioController::class)
        ->middleware('rol:administrador');

    // ── Dashboards por rol ───────────────────────────────
    Route::get('/admin',     fn() => view('dashboards.admin'))
        ->middleware('rol:administrador')
        ->name('admin.dashboard');

    Route::get('/caja',      fn() => view('dashboards.caja'))
        ->middleware('rol:administrador,caja')
        ->name('caja.dashboard');

    Route::get('/recepcion', fn() => view('dashboards.recepcion'))
        ->middleware('rol:administrador,recepcion')
        ->name('recepcion.dashboard');

    Route::prefix('familias')->name('familias.')->group(function () {

        // Gestión de acceso al portal — solo administrador
        Route::post('contactos/{contactoId}/habilitar-portal',   [FamiliaController::class, 'habilitarPortal'])
            ->middleware('rol:administrador')
            ->name('contactos.habilitar-portal');

        Route::post('contactos/{contactoId}/deshabilitar-portal',[FamiliaController::class, 'deshabilitarPortal'])
            ->middleware('rol:administrador')
            ->name('contactos.deshabilitar-portal');

        Route::post('contactos/{contactoId}/crear-usuario',      [FamiliaController::class, 'crearUsuario'])
            ->middleware('rol:administrador')
            ->name('contactos.crear-usuario');

        Route::post('contactos/{contactoId}/resetear-password',  [FamiliaController::class, 'resetearPassword'])
            ->middleware('rol:administrador')
            ->name('contactos.resetear-password');

        // Contactos de una familia (AJAX)
        Route::get('{id}/contactos', [FamiliaController::class, 'contactos'])
            ->middleware('rol:administrador,recepcion')
            ->name('contactos');
        // Actualizar datos de un contacto (AJAX desde edit de alumno)
        Route::put('contactos/{contactoId}', [\App\Http\Controllers\FamiliaController::class, 'actualizarContacto'])
            ->middleware('rol:administrador,recepcion')
            ->name('contactos.update');
        // Crear nuevo contacto para un alumno (AJAX desde edit de alumno)
        Route::post('contactos', [\App\Http\Controllers\FamiliaController::class, 'agregarContacto'])
            ->middleware('rol:administrador,recepcion')
            ->name('contactos.store');
        Route::delete('contactos/{contactoId}', [\App\Http\Controllers\FamiliaController::class, 'eliminarContacto'])
            ->middleware('rol:administrador,recepcion')
            ->name('contactos.destroy');
    });

    // Resource de familias — admin y recepción ven, solo admin crea/edita
    Route::resource('familias', FamiliaController::class)
        ->middleware('rol:administrador,recepcion')
        ->only(['index', 'show', 'create', 'store', 'edit', 'update']);

    // =======================================================
    // Endpoints generados:
    //
    // GET    /familias                                    → index
    // GET    /familias/create                             → create (solo admin)
    // POST   /familias                                    → store  (solo admin)
    // GET    /familias/{id}                               → show
    // GET    /familias/{id}/edit                          → edit   (solo admin)
    // PUT    /familias/{id}                               → update (solo admin)
    // GET    /familias/{id}/contactos                     → contactos (AJAX)
    // POST   /familias/contactos/{id}/habilitar-portal    → habilitarPortal
    // POST   /familias/contactos/{id}/deshabilitar-portal → deshabilitarPortal
    // POST   /familias/contactos/{id}/crear-usuario       → crearUsuario
    // POST   /familias/contactos/{id}/resetear-password   → resetearPassword
    // =======================================================
});
// ── Cobros / Caja ─────────────────────────────────────
Route::middleware('rol:administrador,caja')->prefix('cobros')->name('cobros.')->group(function () {

    // Buscador de alumno
    Route::get('/',                        [CobrosController::class, 'index'])
        ->name('index');

    // Autocomplete AJAX
    Route::get('/buscar-alumno',           [CobrosController::class, 'buscarAlumno'])
        ->name('buscar');

    // Pantalla de cobro del alumno
    Route::get('/alumno/{alumnoId}',       [CobrosController::class, 'alumno'])
        ->name('alumno');

    // Procesar pago
    Route::post('/registrar',              [CobrosController::class, 'registrar'])
        ->name('registrar');

    // Recibo generado
    Route::get('/recibo/{pagoId}',         [CobrosController::class, 'recibo'])
        ->name('recibo');
});
// =======================================================
// Portal de padres de familia
// =======================================================
Route::middleware(['auth', 'rol:padre', 'force.json.on.ajax'])
    ->prefix('portal')
    ->name('portal.')
    ->group(function () {

        Route::get('/',                               fn() => view('portal.dashboard'))->name('dashboard');
        Route::get('/hijos',                          [PortalPadreController::class, 'hijos'])->name('hijos');
        Route::get('/hijos/{alumnoId}/estado-cuenta', [PortalPadreController::class, 'estadoCuenta'])->name('estado-cuenta');
        Route::get('/hijos/{alumnoId}/pagos', [PortalPadreController::class, 'historialPagos'])->name('historial-pagos');
        Route::get('/razones-sociales', [PortalPadreController::class, 'razonesSociales'])->name('razones-sociales');
    });
