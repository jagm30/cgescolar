<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CicloEscolarController;
use App\Http\Controllers\ConceptoController;
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




Route::get('/tables', function () {return view('plantilla.tables');})->name('tables');
Route::get('/datatables', function () {return view('plantilla.data');})->name('datatables');
Route::get('/ui-elements', function () {return view('plantilla.uiGeneral');})->name('ui-elements');
Route::get('/forms', function () {return view('plantilla.forms');})->name('forms');
Route::get('/icons', function () {return view('plantilla.icons');})->name('icons');
Route::get('/widgets', function () {return view('plantilla.widgets');})->name('widgets');
// =======================================================
// Rutas públicas — sin autenticación
// =======================================================
Route::middleware('guest')->group(function () {
    Route::get('/',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// =======================================================
// Rutas internas — administrador, caja, recepción
// =======================================================
Route::middleware(['auth', 'force.json.on.ajax'])->group(function () {
    //── Coceptos ───────────────────────────────────────────
    Route::resource('conceptos', ConceptoController::class)
        ->middleware('rol:administrador');

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

    // ── Planes de pago ───────────────────────────────────
    Route::post('/planes/asignar', [PlanPagoController::class, 'asignar'])
        ->middleware('rol:administrador')
        ->name('planes.asignar');

    Route::get('/planes/asignacion/{alumnoId}', [PlanPagoController::class, 'asignacionDeAlumno'])
        ->middleware('rol:administrador,caja')
        ->name('planes.asignacion-alumno');

    Route::resource('planes', PlanPagoController::class)
        ->middleware('rol:administrador');

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
        Route::get('/hijos/{alumnoId}/pagos',         [PortalPadreController::class, 'historialPagos'])->name('historial-pagos');
        Route::get('/razones-sociales',               [PortalPadreController::class, 'razonesSociales'])->name('razones-sociales');
    });

    Route::get('/', function () {
    // 1. Verificamos si el usuario ya tiene una sesión activa
    if (Auth::check()) {
        // 2. Si ya está logueado, lo mandamos a SU dashboard correspondiente
        // (Usando el método del modelo que vimos antes)
        return redirect(Auth::user()->rutaDashboard()); 
    }
    // 3. Si NO está logueado, le mostramos la vista del login normalmente
    return view('login');
    })->name('login');