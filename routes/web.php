<?php

use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BecaController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\CfdiController;
use App\Http\Controllers\CicloEscolarController;
use App\Http\Controllers\CobrosController;
use App\Http\Controllers\ConceptoCobroController;
use App\Http\Controllers\CredencialController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FamiliaController;
use App\Http\Controllers\GradoController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\NivelEscolarController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PlanPagoConceptoController;
use App\Http\Controllers\PlanPagoController;
use App\Http\Controllers\PoliticaController;
use App\Http\Controllers\PortalPadreController;
use App\Http\Controllers\ProspectoController;
use App\Http\Controllers\RazonSocialController;
use App\Http\Controllers\ReporteDeudoresController;
use App\Http\Controllers\ReinscripcionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/tables', function () {
    return view('plantilla.tables');
})->name('tables');
Route::get('/datatables', function () {
    return view('plantilla.data');
})->name('datatables');
Route::get('/ui-elements', function () {
    return view('plantilla.uiGeneral');
})->name('ui-elements');
Route::get('/forms', function () {
    return view('plantilla.forms');
})->name('forms');
Route::get('/icons', function () {
    return view('plantilla.icons');
})->name('icons');
Route::get('/widgets', function () {
    return view('plantilla.widgets');
})->name('widgets');
// =======================================================
// Rutas públicas — sin autenticación
// =======================================================

Route::get('/', [AuthController::class, 'showLogin'])->name('login');

Route::middleware('guest')->group(function () {

    Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit')
    ->middleware('throttle:5,1');
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
    Route::delete('ciclos/{id}/force', [CicloEscolarController::class, 'forceDelete'])
        ->middleware('rol:administrador,caja,recepcion')
        ->name('ciclos.forceDelete');

    Route::resource('ciclos', CicloEscolarController::class)
        ->middleware('rol:administrador');

    // ── Niveles ──────────────────────────────────────────
    // Ruta para el reordenamiento por Drag & Drop
    Route::post('niveles/reordenar', [NivelEscolarController::class, 'reordenar'])
        ->name('niveles.reordenar')
        ->middleware('rol:administrador');
    Route::delete('niveles/{id}/force', [NivelEscolarController::class, 'forceDelete'])
        ->name('niveles.forceDelete');
    Route::resource('niveles', NivelEscolarController::class)
        ->middleware('rol:administrador');

    // ── Grados ───────────────────────────────────────────
    Route::get('grupos/grados-por-ciclo',      [GrupoController::class, 'gradosPorCiclo'])->name('grupos.gradosPorCiclo');
    Route::get('grupos/grupos-por-ciclo-grado', [GrupoController::class, 'gruposPorCicloGrado'])->name('grupos.gruposPorCicloGrado');


    Route::resource('grados', GradoController::class)
        ->middleware('rol:administrador');

    // ── Grupos ───────────────────────────────────────────
    // IMPORTANTE: ruta fija ANTES del resource
    Route::get('descargar-lista-asistencia/{id}', [GrupoController::class, 'generarReporte'])->name('grupos.reporte');

    // Caja puede consultar grupos (solo lectura)
    Route::get('/grupos', [GrupoController::class, 'index'])
        ->middleware('rol:administrador,caja')
        ->name('grupos.index');
    Route::get('/grupos/{grupo}', [GrupoController::class, 'show'])
        ->middleware('rol:administrador,caja')
        ->name('grupos.show');

    Route::post('/grupos/{id}/cambiar-alumno', [GrupoController::class, 'cambiarAlumno'])
        ->middleware('rol:administrador')
        ->name('grupos.cambiar-alumno');
    Route::resource('grupos', GrupoController::class)
        ->middleware('rol:administrador')
        ->except(['index', 'show']);
    Route::patch('grupos/{grupo}/status', [GrupoController::class, 'toggleStatus'])->name('grupos.status');
    Route::post('/grupos/migrar-estructura', [GrupoController::class, 'migrarEstructura'])->name('grupos.migrar');
    Route::post('/grupos/{grupo_id}/egresar-todo', [AlumnoController::class, 'egresarTodo'])->name('grupos.egresar-todo');
    // Ruta para procesar la promoción/reinscripción masiva
    Route::post('grupos/promocionar-masivo', [GrupoController::class, 'promocionarMasivo'])
        ->name('grupos.promocionar-masivo');


    // ── Alumnos ──────────────────────────────────────────
    // Rutas extra ANTES del resource
    Route::get('/alumnos/{id}/hermanos', [AlumnoController::class, 'hermanos'])
        ->middleware('rol:administrador,recepcion')
        ->name('alumnos.hermanos');

    Route::get('/alumnos/{id}/estado-cuenta', [AlumnoController::class, 'estadoCuenta'])
        ->middleware('rol:administrador,caja,recepcion')
        ->name('alumnos.estado-cuenta');

    // Caja puede ver el índice y perfil de alumnos (solo lectura)
    Route::get('/alumnos', [AlumnoController::class, 'index'])
        ->middleware('rol:administrador,recepcion,caja')
        ->name('alumnos.index');
    // Operaciones de escritura — solo admin y recepción
    // IMPORTANTE: el resource (que incluye /alumnos/create) debe ir ANTES
    // de la ruta /alumnos/{alumno} para evitar que "create" se resuelva como {id}
    Route::resource('alumnos', AlumnoController::class)
        ->middleware('rol:administrador,recepcion')
        ->except(['index', 'show']);
    Route::get('/alumnos/{alumno}', [AlumnoController::class, 'show'])
        ->middleware('rol:administrador,recepcion,caja')
        ->name('alumnos.show');

    Route::delete('/inscripciones/{id}', [AlumnoController::class, 'quitarDelGrupo'])->name('inscripciones.destroy');
    Route::patch('/alumnos/{id}/dar-baja', [AlumnoController::class, 'darBaja'])->name('alumnos.darBaja');
    Route::get('alumnos/{id}/reporte', [AlumnoController::class, 'reporteAlumno'])->name('alumnos.reporte');
    Route::get('/alumnos-bajas', [AlumnoController::class, 'reporteBajas'])
        ->middleware('rol:administrador,recepcion')
        ->name('alumnos.bajas');
    Route::post('/alumnos/{id}/inscripcion-anticipada', [AlumnoController::class, 'registrarAnticipada'])
        ->middleware('rol:administrador,recepcion')
        ->name('alumnos.inscripcion-anticipada');

    // ── Reinscripciones ──────────────────────────────────
    Route::middleware('rol:administrador,recepcion')->prefix('reinscripciones')->name('reinscripciones.')->group(function () {
        Route::get('/',       [ReinscripcionController::class, 'index'])->name('index');
        Route::get('/buscar', [ReinscripcionController::class, 'buscar'])->name('buscar');
        Route::post('/',      [ReinscripcionController::class, 'store'])->name('store');
    });

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
            Route::get('/', [PoliticaController::class, 'index'])->name('index');

            // Descuentos
            Route::post('descuento', [PoliticaController::class, 'storeDescuento'])->name('descuento.store');
            Route::put('descuento/{id}', [PoliticaController::class, 'updateDescuento'])->name('descuento.update');
            Route::delete('descuento/{id}', [PoliticaController::class, 'destroyDescuento'])->name('descuento.destroy');

            // Recargo
            Route::post('recargo', [PoliticaController::class, 'storeRecargo'])->name('recargo.store');
            Route::put('recargo/{id}', [PoliticaController::class, 'updateRecargo'])->name('recargo.update');
            Route::delete('recargo/{id}', [PoliticaController::class, 'destroyRecargo'])->name('recargo.destroy');
        });
    // ── Planes de pago ───────────────────────────────────
    Route::get('/planes/asignar', [PlanPagoController::class, 'indexAsignaciones'])
        ->middleware('rol:administrador')
        ->name('planes.asignar.index');

    Route::get('/planes/asignar/nuevo', [PlanPagoController::class, 'createAsignacion'])
        ->middleware('rol:administrador')
        ->name('planes.asignar.form');

    Route::get('/planes/asignar/disponibles', [PlanPagoController::class, 'planesDisponibles'])
        ->middleware('rol:administrador')
        ->name('planes.asignar.disponibles');

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
        Route::get('conceptos', [PlanPagoConceptoController::class, 'index'])->name('conceptos.index');
        Route::post('conceptos', [PlanPagoConceptoController::class, 'store'])->name('conceptos.store');
        Route::put('conceptos/{id}', [PlanPagoConceptoController::class, 'update'])->name('conceptos.update');
        Route::delete('conceptos/{id}', [PlanPagoConceptoController::class, 'destroy'])->name('conceptos.destroy');

        // Políticas (descuentos + recargo)
        Route::get('politicas', [PoliticaController::class, 'index'])->name('politicas.index');
        Route::post('politicas/descuento', [PoliticaController::class, 'storeDescuento'])->name('politicas.descuento.store');
        Route::put('politicas/descuento/{id}', [PoliticaController::class, 'updateDescuento'])->name('politicas.descuento.update');
        Route::delete('politicas/descuento/{id}', [PoliticaController::class, 'destroyDescuento'])->name('politicas.descuento.destroy');
        Route::post('politicas/recargo', [PoliticaController::class, 'storeRecargo'])->name('politicas.recargo.store');
        Route::put('politicas/recargo/{id}', [PoliticaController::class, 'updateRecargo'])->name('politicas.recargo.update');
        Route::delete('politicas/recargo/{id}', [PoliticaController::class, 'destroyRecargo'])->name('politicas.recargo.destroy');
    })->middleware('rol:administrador');

    // ── Cargos ───────────────────────────────────────────
    Route::get('/cargos/{id}/preview', [CargoController::class, 'preview'])
        ->middleware('rol:administrador,caja')
        ->name('cargos.preview');

    Route::resource('cargos', CargoController::class)
        ->only(['index', 'show'])
        ->middleware('rol:administrador,caja');

    Route::delete('/cargos/{id}', [CargoController::class, 'destroy'])
        ->middleware('rol:administrador')
        ->name('cargos.destroy');

    // ── Pagos ────────────────────────────────────────────
    Route::get('/pagos/corte', [PagoController::class, 'corte'])
        ->middleware('rol:administrador,caja')
        ->name('pagos.corte');

    Route::get('/pagos/detalle-ingresos', [PagoController::class, 'detalleIngresos'])
        ->middleware('rol:administrador,caja')
        ->name('pagos.detalle-ingresos');

    Route::post('/pagos/{id}/anular', [PagoController::class, 'anular'])
        ->middleware('rol:administrador')
        ->name('pagos.anular');

    Route::get('/pagos/{pago}/form-factura', [PagoController::class, 'formFactura'])
        ->middleware('rol:administrador,caja')
        ->name('pagos.form-factura');

    Route::resource('pagos', PagoController::class)
        ->only(['index', 'show', 'create', 'store'])
        ->middleware('rol:administrador,caja');

    // ── Becas ────────────────────────────────────────────
    Route::get('/becas/catalogo', [BecaController::class, 'catalogo'])
        ->middleware('rol:administrador')
        ->name('becas.catalogo');

    Route::post('/becas/catalogo', [BecaController::class, 'storeCatalogo'])
        ->middleware('rol:administrador')
        ->name('becas.catalogo.store');

    Route::get('/becas/catalogo/{id}/editar', [BecaController::class, 'editCatalogo'])
        ->middleware('rol:administrador')
        ->name('becas.catalogo.edit');

    Route::put('/becas/catalogo/{id}', [BecaController::class, 'updateCatalogo'])
        ->middleware('rol:administrador')
        ->name('becas.catalogo.update');

    Route::delete('/becas/catalogo/{id}', [BecaController::class, 'destroyCatalogo'])
        ->middleware('rol:administrador')
        ->name('becas.catalogo.destroy');

    Route::get('/becas/crear', [BecaController::class, 'create'])
        ->middleware('rol:administrador')
        ->name('becas.create');

    Route::get('/becas/alumno/{alumnoId}/becas-activas', [BecaController::class, 'alumnoBecasActivas'])
        ->middleware('rol:administrador')
        ->name('becas.alumno.becas');

    Route::resource('becas', BecaController::class)
        ->only(['index', 'store', 'destroy'])
        ->middleware('rol:administrador');

    // ── Prospectos ───────────────────────────────────────
    Route::get('/prospectos/metricas', [ProspectoController::class, 'metricas'])
        ->middleware('rol:administrador,recepcion')
        ->name('prospectos.metricas');

    Route::post('/prospectos/{id}/etapa', [ProspectoController::class, 'cambiarEtapa'])
        ->middleware('rol:administrador,recepcion')
        ->name('prospectos.etapa');

    Route::post('/prospectos/{id}/seguimiento', [ProspectoController::class, 'agregarSeguimiento'])
        ->middleware('rol:administrador,recepcion')
        ->name('prospectos.seguimiento');

    Route::post('/prospectos/{id}/documentos', [ProspectoController::class, 'agregarDocumento'])
        ->middleware('rol:administrador,recepcion')
        ->name('prospectos.documentos.store');

    Route::get('/prospectos/{id}/documentos/{documentoId}/archivo', [ProspectoController::class, 'descargarDocumento'])
        ->middleware('rol:administrador,recepcion')
        ->name('prospectos.documentos.archivo');

    Route::resource('prospectos', ProspectoController::class)
        ->only(['index', 'show', 'create', 'store'])
        ->middleware('rol:administrador,recepcion');

    // ── Usuarios ─────────────────────────────────────────
    Route::post('usuarios/generar-masivos', [UsuarioController::class, 'generarUsuariosMasivos'])
        ->middleware('rol:administrador')
        ->name('usuarios.generarMasivos');

    Route::post('usuarios/{id}/reactivar', [UsuarioController::class, 'reactivar'])
        ->middleware('rol:administrador')
        ->name('usuarios.reactivar');

    Route::get('/usuarios/pendientes-portal', [UsuarioController::class, 'pendientesPortal'])
        ->middleware('rol:administrador')
        ->name('usuarios.pendientes-portal');

    Route::get('usuarios/credenciales-pdf', [UsuarioController::class, 'descargarCredencialesPdf'])
        ->middleware('rol:administrador')
        ->name('usuarios.credencialesPdf');

    Route::get('usuarios/credenciales-pdf', [UsuarioController::class, 'descargarCredencialesPdf'])
        ->middleware('rol:administrador')
        ->name('usuarios.credencialesPdf');

    Route::delete('usuarios/{id}/forzar-eliminar', [UsuarioController::class, 'forzarEliminar'])
        ->middleware('rol:administrador')
        ->name('usuarios.forzarEliminar');

    Route::get('/perfil', [UsuarioController::class, 'perfil'])
        ->name('usuarios.perfil');

    Route::post('/perfil/foto', [UsuarioController::class, 'actualizarFoto'])
        ->name('usuarios.perfil.foto');

    Route::resource('usuarios', UsuarioController::class)
        ->middleware('rol:administrador');

    // ── Dashboards por rol ───────────────────────────────
    Route::get('/admin', [DashboardController::class, 'admin'])
        ->middleware('rol:administrador')
        ->name('admin.dashboard');

    Route::get('/caja', [DashboardController::class, 'caja'])
        ->middleware('rol:administrador,caja')
        ->name('caja.dashboard');

    Route::get('/recepcion', fn() => view('dashboards.recepcion'))
        ->middleware('rol:administrador,recepcion')
        ->name('recepcion.dashboard');

    Route::prefix('familias')->name('familias.')->group(function () {

        // Gestión de acceso al portal — solo administrador
        Route::post('contactos/{contactoId}/habilitar-portal', [FamiliaController::class, 'habilitarPortal'])
            ->middleware('rol:administrador')
            ->name('contactos.habilitar-portal');

        Route::post('contactos/{contactoId}/deshabilitar-portal', [FamiliaController::class, 'deshabilitarPortal'])
            ->middleware('rol:administrador')
            ->name('contactos.deshabilitar-portal');

        Route::post('contactos/{contactoId}/crear-usuario', [FamiliaController::class, 'crearUsuario'])
            ->middleware('rol:administrador')
            ->name('contactos.crear-usuario');

        Route::post('contactos/{contactoId}/resetear-password', [FamiliaController::class, 'resetearPassword'])
            ->middleware('rol:administrador')
            ->name('contactos.resetear-password');

        // Contactos de una familia (AJAX)
        Route::get('{id}/contactos', [FamiliaController::class, 'contactos'])
            ->middleware('rol:administrador,recepcion')
            ->name('contactos');
        // Datos de contactos para pre-llenar formulario de creación de alumno (AJAX)
        Route::get('{id}/contactos-enlace', [FamiliaController::class, 'contactosParaEnlace'])
            ->middleware('rol:administrador,recepcion')
            ->name('contactos.enlace');
        // Actualizar datos de un contacto (AJAX desde edit de alumno)
        Route::put('contactos/{contactoId}', [FamiliaController::class, 'actualizarContacto'])
            ->middleware('rol:administrador,recepcion')
            ->name('contactos.update');
        // Subir/cambiar foto de un contacto existente (AJAX)
        Route::post('contactos/{contactoId}/foto', [FamiliaController::class, 'subirFotoContacto'])
            ->middleware('rol:administrador,recepcion')
            ->name('contactos.foto');
        // Crear nuevo contacto para un alumno (AJAX desde edit de alumno)
        Route::post('contactos', [FamiliaController::class, 'agregarContacto'])
            ->middleware('rol:administrador,recepcion')
            ->name('contactos.store');
        Route::delete('contactos/{contactoId}', [FamiliaController::class, 'eliminarContacto'])
            ->middleware('rol:administrador,recepcion')
            ->name('contactos.destroy');

        // Razones sociales (datos de facturación) — admin y caja
        Route::post('razon-social', [RazonSocialController::class, 'store'])
            ->middleware('rol:administrador,caja')
            ->name('razon-social.store');

        Route::put('razon-social/{id}', [RazonSocialController::class, 'update'])
            ->middleware('rol:administrador,caja')
            ->name('razon-social.update');

        Route::delete('razon-social/{id}', [RazonSocialController::class, 'destroy'])
            ->middleware('rol:administrador,caja')
            ->name('razon-social.destroy');

        Route::post('razon-social/{id}/principal', [RazonSocialController::class, 'setPrincipal'])
            ->middleware('rol:administrador,caja')
            ->name('razon-social.principal');
    });

    // Resource de familias — caja solo consulta, recepción ve y gestiona, admin hace todo
    Route::get('/familias', [FamiliaController::class, 'index'])
        ->middleware('rol:administrador,recepcion,caja')
        ->name('familias.index');
    Route::resource('familias', FamiliaController::class)
        ->middleware('rol:administrador,recepcion')
        ->only(['create', 'store', 'edit', 'update']);
    Route::get('/familias/{familia}', [FamiliaController::class, 'show'])
        ->middleware('rol:administrador,recepcion,caja')
        ->name('familias.show');

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
// ── CFDI / Facturación ────────────────────────────────
Route::middleware('rol:administrador,caja')->get('/facturas', [CfdiController::class, 'index'])->name('facturas.index');

Route::middleware('rol:administrador,caja')->prefix('cfdis')->name('cfdis.')->group(function () {
    Route::post('/emitir/{pago}', [CfdiController::class, 'emitir'])->name('emitir');
    Route::get('/preview-global', [CfdiController::class, 'previewGlobal'])->name('preview-global');
    Route::post('/emitir-global', [CfdiController::class, 'emitirGlobal'])->name('emitir-global');
    Route::post('/{cfdi}/cancelar', [CfdiController::class, 'cancelar'])->name('cancelar');
    Route::get('/{cfdi}/descargar/{formato}', [CfdiController::class, 'descargar'])->name('descargar');
    Route::get('/{cfdi}/form-correo', [CfdiController::class, 'formCorreo'])->name('form-correo');
    Route::post('/{cfdi}/enviar-correo', [CfdiController::class, 'enviarCorreo'])->name('enviar-correo');
});

// ── Cobros / Caja ─────────────────────────────────────
Route::middleware('rol:administrador,caja')->prefix('cobros')->name('cobros.')->group(function () {

    // Buscador de alumno
    Route::get('/', [CobrosController::class, 'index'])
        ->name('index');

    // Autocomplete AJAX
    Route::get('/buscar-alumno', [CobrosController::class, 'buscarAlumno'])
        ->name('buscar');

    // Pantalla de cobro del alumno
    Route::get('/alumno/{alumnoId}', [CobrosController::class, 'alumno'])
        ->name('alumno');

    // Procesar pago
    Route::post('/registrar', [CobrosController::class, 'registrar'])
        ->name('registrar');

    // Recibo generado
    Route::get('/recibo/{pagoId}', [CobrosController::class, 'recibo'])
        ->name('recibo');

    Route::get('/recibo/{pagoId}/pdf', [CobrosController::class, 'descargarPdf'])->name('pdf');
});
// =======================================================
// Portal de padres de familia
// =======================================================
Route::middleware(['auth', 'rol:padre', 'force.json.on.ajax'])
    ->prefix('portal')
    ->name('portal.')
    ->group(function () {

        Route::get('/', [PortalPadreController::class, 'dashboard'])->name('dashboard');
        Route::get('/hijos', [PortalPadreController::class, 'hijos'])->name('hijos');
        Route::get('/hijos/{alumnoId}/estado-cuenta', [PortalPadreController::class, 'estadoCuenta'])->name('estado-cuenta');
        Route::get('/hijos/{alumnoId}/pagos', [PortalPadreController::class, 'historialPagos'])->name('historial-pagos');
        Route::get('/cfdis/{cfdiId}/descargar/{formato}', [PortalPadreController::class, 'descargarCfdi'])->name('cfdis.descargar');
        Route::get('/razones-sociales', [PortalPadreController::class, 'razonesSociales'])->name('razones-sociales');
    });

// =======================================================
// Rutas para  configuración general (nombre del colegio, logo, etc.)
// =======================================================
// Agrupamos las rutas de configuración
Route::middleware('rol:administrador,recepcion')->prefix('configuracion')->group(function () {
    Route::get('/', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/actualizar', [SettingController::class, 'update'])->name('settings.update');
});
// =======================================================
// Rutas para diseño de credenciales
// =======================================================
Route::middleware('rol:administrador,recepcion')->prefix('credenciales')->group(function () {
    Route::get('/', [CredencialController::class, 'index'])->name('credenciales.index');

    // RUTAS ESTÁTICAS Y DE MÚLTIPLES PARÁMETROS (Siempre van arriba)
    Route::get('/imprimir-lote/{credencial_id}/{grupo_id}', [CredencialController::class, 'imprimirLote'])->name('credenciales.imprimirLote');
    // Ruta para imprimir a un solo alumno
    Route::get('/individual/{credencial}/{alumno}', [CredencialController::class, 'imprimirIndividual'])
        ->name('credenciales.imprimirIndividual');
    Route::get('/credenciales/{credencial}/preview/{alumno}', [CredencialController::class, 'previewEnEditor'])
        ->name('credenciales.previewEnEditor');
    Route::get('/preview/{credencial_id}/{alumno_id}', [CredencialController::class, 'preview'])->name('credenciales.preview');

    // RUTAS BÁSICAS
    Route::post('/store', [CredencialController::class, 'store'])->name('credenciales.store');

    // RUTAS QUE PIDEN UN {id} (Siempre van abajo para que no choquen)
    Route::get('/{id}/edit', [CredencialController::class, 'edit'])->name('credenciales.edit');
    Route::post('/{id}/config', [CredencialController::class, 'updateConfig'])->name('credenciales.updateConfig');
    Route::post('/{id}/upload-fondo', [CredencialController::class, 'uploadFondo'])->name('credenciales.uploadFondo');
    Route::delete('/{id}', [CredencialController::class, 'destroy'])->name('credenciales.destroy');
});
// ── Reportes ─────────────────────────────────────────
Route::get('/reportes/deudores', [ReporteDeudoresController::class, 'index'])
    ->middleware(['auth', 'force.json.on.ajax', 'rol:administrador,caja'])
    ->name('reportes.deudores');

// Configuración fiscal (datos del emisor para CFDI)
Route::post('/fiscal', [SettingController::class, 'updateFiscal'])
    ->middleware(['auth', 'force.json.on.ajax', 'rol:administrador'])
    ->name('settings.fiscal');

// Verificar conexión con factura.com (diagnóstico de series)
Route::get('/fiscal/verificar-series', [SettingController::class, 'verificarSeries'])
    ->middleware(['auth', 'rol:administrador'])
    ->name('settings.verificarSeries');
