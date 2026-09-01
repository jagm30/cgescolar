<?php

/**
 * Suite completa de pruebas para el módulo de cobros.
 *
 * Cubre:
 *  1. Nombres de meses en cargos (periodo_label en español)
 *  2. Sin colegiaturas duplicadas del mismo mes
 *  3. Descuentos por condonaciones (no se aplican dos veces)
 *  4. Recargos por mora — porcentaje, monto_fijo, tope_maximo, meses_exentos, acumular_mensual
 *  5. Reporte de corte: datos correctos (montos, nombres, grados, grupos, forma de pago, totales)
 *  6. Consistencia de historial de pagos (pagos anulados no cuentan)
 *  7. Estados de cuenta: saldo_pendiente y total_a_pagar_hoy correctos
 */

use App\Models\Alumno;
use App\Models\AsignacionPlan;
use App\Models\BecaAlumno;
use App\Models\Cargo;
use App\Models\CatalogoBeca;
use App\Models\CicloEscolar;
use App\Models\Condonacion;
use App\Models\CondonacionDetalle;
use App\Models\ConceptoCobro;
use App\Models\DescuentoCargo;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\NivelEscolar;
use App\Models\Pago;
use App\Models\PagoDetalle;
use App\Models\PlanPago;
use App\Models\PlanPagoConcepto;
use App\Models\PoliticaDescuento;
use App\Models\PoliticaRecargo;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─────────────────────────────────────────────────────────────────────────────
// Helpers reutilizables (a nivel de archivo, fuera de describe)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Crea el andamiaje mínimo para pruebas de cobros:
 * admin, ciclo, nivel, grado, grupo, alumno, inscripción, concepto y plan.
 */
function crearContextoCobros(array $overrides = []): array
{
    $admin = Usuario::create([
        'nombre'        => 'Cajero Test',
        'email'         => fake()->unique()->safeEmail(),
        'password_hash' => bcrypt('password'),
        'rol'           => 'administrador',
        'activo'        => true,
    ]);

    $ciclo = CicloEscolar::create([
        'nombre'       => '2026-2027',
        'fecha_inicio' => '2026-08-01',
        'fecha_fin'    => '2027-07-31',
        'estado'       => 'activo',
    ]);

    $nivel = NivelEscolar::create([
        'nombre' => $overrides['nivel_nombre'] ?? 'Primaria',
        'revoe'  => fake()->unique()->numerify('REV####'),
        'orden'  => 1,
        'activo' => true,
    ]);

    $grado = Grado::create([
        'nivel_id' => $nivel->id,
        'numero'   => $overrides['grado_numero'] ?? 3,
    ]);

    $grupo = Grupo::create([
        'ciclo_id' => $ciclo->id,
        'grado_id' => $grado->id,
        'nombre'   => $overrides['grupo_nombre'] ?? 'B',
        'activo'   => true,
    ]);

    $alumno = Alumno::create([
        'matricula'        => fake()->unique()->numerify('A####'),
        'nombre'           => $overrides['alumno_nombre'] ?? 'María',
        'ap_paterno'       => $overrides['alumno_ap_paterno'] ?? 'López',
        'fecha_nacimiento' => '2015-05-10',
        'estado'           => 'activo',
    ]);

    $inscripcion = Inscripcion::create([
        'alumno_id' => $alumno->id,
        'ciclo_id'  => $ciclo->id,
        'grupo_id'  => $grupo->id,
        'fecha'     => '2026-08-01',
        'activo'    => true,
    ]);

    $concepto = ConceptoCobro::create([
        'nombre'         => $overrides['concepto_nombre'] ?? 'Colegiatura',
        'tipo'           => $overrides['concepto_tipo'] ?? 'colegiatura',
        'aplica_beca'    => $overrides['aplica_beca'] ?? true,
        'aplica_recargo' => $overrides['aplica_recargo'] ?? true,
        'activo'         => true,
    ]);

    $plan = PlanPago::create([
        'ciclo_id'     => $ciclo->id,
        'nivel_id'     => $nivel->id,
        'nombre'       => 'Plan Primaria 2026',
        'periodicidad' => 'mensual',
        'fecha_inicio' => '2026-08-01',
        'fecha_fin'    => '2027-07-31',
        'activo'       => true,
    ]);

    PlanPagoConcepto::create([
        'plan_id'     => $plan->id,
        'concepto_id' => $concepto->id,
        'monto'       => $overrides['monto_plan'] ?? 2000,
    ]);

    $asignacion = AsignacionPlan::create([
        'plan_id'      => $plan->id,
        'alumno_id'    => $alumno->id,
        'origen'       => 'individual',
        'fecha_inicio' => '2026-08-01',
        'fecha_fin'    => '2027-07-31',
    ]);

    return compact(
        'admin', 'ciclo', 'nivel', 'grado', 'grupo',
        'alumno', 'inscripcion', 'concepto', 'plan', 'asignacion'
    );
}

/** Crea un cargo para el contexto dado. */
function crearCargo(array $ctx, array $overrides = []): Cargo
{
    return Cargo::create([
        'inscripcion_id'    => $ctx['inscripcion']->id,
        'concepto_id'       => $ctx['concepto']->id,
        'asignacion_id'     => $ctx['asignacion']->id,
        'monto_original'    => $overrides['monto_original'] ?? 2000,
        'fecha_vencimiento' => $overrides['fecha_vencimiento'] ?? '2026-09-10',
        'estado'            => $overrides['estado'] ?? 'pendiente',
        'periodo'           => $overrides['periodo'] ?? '2026-09',
    ]);
}

/** Crea una política de recargo ligada al plan del contexto. */
function crearPoliticaRecargo(array $ctx, array $opciones = []): PoliticaRecargo
{
    return PoliticaRecargo::create([
        'plan_id'          => $ctx['plan']->id,
        'dia_limite_pago'  => $opciones['dia_limite_pago'] ?? 10,
        'tipo_recargo'     => $opciones['tipo_recargo'] ?? 'porcentaje',
        'valor'            => $opciones['valor'] ?? 5,
        'tope_maximo'      => $opciones['tope_maximo'] ?? null,
        'activo'           => true,
        'acumular_mensual' => $opciones['acumular_mensual'] ?? false,
        'meses_exentos'    => $opciones['meses_exentos'] ?? [],
    ]);
}

/**
 * Datos del POST para registrar un pago en cobros.registrar.
 * Retorna el array listo para ser enviado con $this->post(...).
 */
function datosRegistrarPago(array $ctx, Cargo $cargo, array $montos = []): array
{
    return [
        'alumno_id'  => $ctx['alumno']->id,
        'forma_pago' => $montos['forma_pago'] ?? 'efectivo',
        'fecha_pago' => $montos['fecha_pago'] ?? '2026-09-05',
        'items'      => [[
            'tipo'                  => 'cargo',
            'cargo_id'              => $cargo->id,
            'monto_abonado'         => $montos['monto_abonado'] ?? (float) $cargo->monto_original,
            'descuento_beca'        => $montos['descuento_beca'] ?? 0,
            'descuento_pronto_pago' => $montos['descuento_pronto_pago'] ?? 0,
            'descuento_otros'       => $montos['descuento_otros'] ?? 0,
            'recargo'               => $montos['recargo'] ?? 0,
        ]],
    ];
}

// ═════════════════════════════════════════════════════════════════════════════
// 1. NOMBRES DE MESES EN CARGOS
// ═════════════════════════════════════════════════════════════════════════════

describe('Nombres de meses en cargos (periodo_label)', function () {
    test('periodo_label devuelve el mes correcto en español para cada mes del año', function (string $periodo, string $esperado) {
        $ctx   = crearContextoCobros();
        $cargo = crearCargo($ctx, ['periodo' => $periodo]);

        expect($cargo->periodo_label)->toBe($esperado);
    })->with([
        ['2026-01', 'Enero 2026'],
        ['2026-02', 'Febrero 2026'],
        ['2026-03', 'Marzo 2026'],
        ['2026-04', 'Abril 2026'],
        ['2026-05', 'Mayo 2026'],
        ['2026-06', 'Junio 2026'],
        ['2026-07', 'Julio 2026'],
        ['2026-08', 'Agosto 2026'],
        ['2026-09', 'Septiembre 2026'],
        ['2026-10', 'Octubre 2026'],
        ['2026-11', 'Noviembre 2026'],
        ['2026-12', 'Diciembre 2026'],
    ]);

    test('periodo_label para cargo_recurrente (YYYY-MM-NN) extrae solo YYYY-MM', function () {
        $ctx   = crearContextoCobros(['concepto_tipo' => 'cargo_recurrente', 'aplica_beca' => false, 'aplica_recargo' => false]);
        $cargo = crearCargo($ctx, ['periodo' => '2026-10-03']);

        expect($cargo->periodo_label)->toBe('Octubre 2026');
    });

    test('etiqueta del cargo incluye concepto y mes en español', function () {
        $ctx   = crearContextoCobros(['concepto_nombre' => 'Colegiatura', 'concepto_tipo' => 'colegiatura']);
        $cargo = crearCargo($ctx, ['periodo' => '2026-11']);

        $cargo->load('concepto');
        expect($cargo->etiqueta)->toBe('Colegiatura Noviembre 2026');
    });

    test('cargo sin periodo devuelve etiqueta solo con nombre del concepto', function () {
        $ctx   = crearContextoCobros(['concepto_nombre' => 'Inscripción', 'concepto_tipo' => 'inscripcion']);
        $cargo = crearCargo($ctx, ['periodo' => '']);

        $cargo->load('concepto');
        expect($cargo->etiqueta)->toBe('Inscripción');
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// 2. SIN COLEGIATURAS DUPLICADAS DEL MISMO MES
// ═════════════════════════════════════════════════════════════════════════════

describe('Sin colegiaturas duplicadas del mismo mes', function () {
    test('no se puede insertar dos cargos del mismo concepto y periodo para la misma inscripcion', function () {
        $ctx = crearContextoCobros();
        crearCargo($ctx, ['periodo' => '2026-09']);

        expect(fn () => crearCargo($ctx, ['periodo' => '2026-09']))->toThrow(\Illuminate\Database\QueryException::class);
    });

    test('se permiten cargos del mismo concepto en distintos periodos', function () {
        $ctx = crearContextoCobros();
        $c1  = crearCargo($ctx, ['periodo' => '2026-09', 'fecha_vencimiento' => '2026-09-10']);
        $c2  = crearCargo($ctx, ['periodo' => '2026-10', 'fecha_vencimiento' => '2026-10-10']);

        expect(Cargo::count())->toBe(2)
            ->and($c1->periodo)->toBe('2026-09')
            ->and($c2->periodo)->toBe('2026-10');
    });

    test('cargo_recurrente permite multiples cargos del mismo concepto en el mismo mes', function () {
        $ctx = crearContextoCobros([
            'concepto_tipo'  => 'cargo_recurrente',
            'aplica_beca'    => false,
            'aplica_recargo' => false,
        ]);

        Cargo::create([
            'inscripcion_id'    => $ctx['inscripcion']->id,
            'concepto_id'       => $ctx['concepto']->id,
            'asignacion_id'     => $ctx['asignacion']->id,
            'monto_original'    => 50,
            'fecha_vencimiento' => '2026-09-01',
            'estado'            => 'pagado',
            'periodo'           => '2026-09-01',
        ]);

        Cargo::create([
            'inscripcion_id'    => $ctx['inscripcion']->id,
            'concepto_id'       => $ctx['concepto']->id,
            'asignacion_id'     => $ctx['asignacion']->id,
            'monto_original'    => 50,
            'fecha_vencimiento' => '2026-09-02',
            'estado'            => 'pagado',
            'periodo'           => '2026-09-02',
        ]);

        expect(Cargo::count())->toBe(2);
    });

    test('pantalla de cobros no muestra cargos pagados como pendientes', function () {
        $ctx   = crearContextoCobros();
        $cargo = crearCargo($ctx);

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo))
            ->assertRedirect();

        $vista  = $this->actingAs($ctx['admin'])
            ->get(route('cobros.alumno', $ctx['alumno']->id));

        $cargos = $vista->viewData('cargos');
        expect($cargos->count())->toBe(0);
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// 3. DESCUENTOS POR CONDONACIONES
// ═════════════════════════════════════════════════════════════════════════════

describe('Descuentos por condonaciones', function () {
    test('la condonación reduce el saldo a pagar en la pantalla de cobros', function () {
        $ctx   = crearContextoCobros(['aplica_beca' => false, 'aplica_recargo' => false]);
        $cargo = crearCargo($ctx, ['monto_original' => 3000]);

        DescuentoCargo::create([
            'cargo_id'       => $cargo->id,
            'tipo'           => 'monto_fijo',
            'valor'          => 1000,
            'monto_aplicado' => 1000,
            'motivo'         => 'Beca institucional',
            'autorizado_por' => $ctx['admin']->id,
            'creado_por'     => $ctx['admin']->id,
        ]);

        $vista      = $this->actingAs($ctx['admin'])
            ->get(route('cobros.alumno', $ctx['alumno']->id));

        $cargoVista = $vista->viewData('cargos')->first();

        expect((float) $cargoVista->descuento_condonacion_calc)->toBe(1000.0)
            ->and((float) $cargoVista->monto_a_pagar_hoy)->toBe(2000.0);
    });

    test('condonacion_ya_aplicada impide aplicar el descuento dos veces en un segundo abono', function () {
        $ctx   = crearContextoCobros(['aplica_beca' => false, 'aplica_recargo' => false]);
        $cargo = crearCargo($ctx, ['monto_original' => 3000, 'fecha_vencimiento' => '2027-01-10', 'periodo' => '2027-01']);

        DescuentoCargo::create([
            'cargo_id'       => $cargo->id,
            'tipo'           => 'monto_fijo',
            'valor'          => 1000,
            'monto_aplicado' => 1000,
            'motivo'         => 'Descuento especial',
            'autorizado_por' => $ctx['admin']->id,
            'creado_por'     => $ctx['admin']->id,
        ]);

        // Primer abono: $500 efectivo + $1,000 condonación = $1,500 monto_abonado
        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo, [
                'fecha_pago'      => '2027-01-05',
                'monto_abonado'   => 500 + 1000,
                'descuento_otros' => 1000,
            ]))->assertRedirect();

        $cargo->refresh();
        expect($cargo->estado)->toBe('parcial');

        // Segunda visita: la condonación ya se aplicó, no debe aparecer de nuevo
        $vista2      = $this->actingAs($ctx['admin'])
            ->get(route('cobros.alumno', $ctx['alumno']->id));

        $cargoVista2 = $vista2->viewData('cargos')->first();
        expect((float) $cargoVista2->descuento_condonacion_calc)->toBe(0.0)
            ->and((float) $cargoVista2->monto_a_pagar_hoy)->toBe(1500.0);
    });

    test('pago completo con condonacion marca el cargo como pagado', function () {
        $ctx   = crearContextoCobros(['aplica_beca' => false, 'aplica_recargo' => false]);
        $cargo = crearCargo($ctx, ['monto_original' => 2000]);

        DescuentoCargo::create([
            'cargo_id'       => $cargo->id,
            'tipo'           => 'monto_fijo',
            'valor'          => 500,
            'monto_aplicado' => 500,
            'motivo'         => 'Apoyo institucional',
            'autorizado_por' => $ctx['admin']->id,
            'creado_por'     => $ctx['admin']->id,
        ]);

        // Abono total: efectivo (1500) + condonación (500) = 2000
        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo, [
                'monto_abonado'   => 1500 + 500,
                'descuento_otros' => 500,
            ]))->assertRedirect();

        $cargo->refresh();
        expect($cargo->estado)->toBe('pagado');
    });

    test('cancelar condonacion activa revierte el descuento en la pantalla de cobros', function () {
        $ctx   = crearContextoCobros(['aplica_beca' => false, 'aplica_recargo' => false]);
        $cargo = crearCargo($ctx, ['monto_original' => 2000]);

        $condonacion = Condonacion::create([
            'alumno_id'   => $ctx['alumno']->id,
            'ciclo_id'    => $ctx['ciclo']->id,
            'monto_total' => 800,
            'motivo'      => 'Apoyo especial de beca',
            'estado'      => 'activa',
            'creado_por'  => $ctx['admin']->id,
        ]);

        $descuento = DescuentoCargo::create([
            'cargo_id'       => $cargo->id,
            'tipo'           => 'monto_fijo',
            'valor'          => 800,
            'monto_aplicado' => 800,
            'motivo'         => 'Apoyo especial de beca',
            'autorizado_por' => $ctx['admin']->id,
            'creado_por'     => $ctx['admin']->id,
        ]);

        CondonacionDetalle::create([
            'condonacion_id'     => $condonacion->id,
            'cargo_id'           => $cargo->id,
            'descuento_cargo_id' => $descuento->id,
            'monto_aplicado'     => 800,
        ]);

        // Verificar que el descuento aparece antes de cancelar
        $vista1 = $this->actingAs($ctx['admin'])
            ->get(route('cobros.alumno', $ctx['alumno']->id));

        expect((float) $vista1->viewData('cargos')->first()->descuento_condonacion_calc)->toBe(800.0);

        // Cancelar la condonación vía controller
        $this->actingAs($ctx['admin'])
            ->delete(route('condonaciones.destroy', $condonacion->id))
            ->assertRedirect();

        // Después de cancelar: el descuento no debe aparecer
        $vista2 = $this->actingAs($ctx['admin'])
            ->get(route('cobros.alumno', $ctx['alumno']->id));

        expect((float) $vista2->viewData('cargos')->first()->descuento_condonacion_calc)->toBe(0.0);
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// 4. RECARGOS POR MORA
// ═════════════════════════════════════════════════════════════════════════════

describe('Recargos por mora', function () {
    test('PoliticaRecargo::calcular aplica porcentaje correctamente', function () {
        $ctx = crearContextoCobros();
        $pr  = crearPoliticaRecargo($ctx, ['tipo_recargo' => 'porcentaje', 'valor' => 10]);

        expect($pr->calcular(2000))->toBe(200.0);
    });

    test('PoliticaRecargo::calcular aplica monto_fijo correctamente', function () {
        $ctx = crearContextoCobros();
        $pr  = crearPoliticaRecargo($ctx, ['tipo_recargo' => 'monto_fijo', 'valor' => 150]);

        expect($pr->calcular(2000))->toBe(150.0);
    });

    test('PoliticaRecargo::calcular respeta tope_maximo', function () {
        $ctx = crearContextoCobros();
        $pr  = crearPoliticaRecargo($ctx, [
            'tipo_recargo' => 'porcentaje',
            'valor'        => 20,
            'tope_maximo'  => 300,
        ]);

        // 20% de $2000 = $400, pero tope es $300
        expect($pr->calcular(2000))->toBe(300.0);
    });

    test('PoliticaRecargo::calcular acumula por meses de retraso', function () {
        $ctx = crearContextoCobros();
        $pr  = crearPoliticaRecargo($ctx, [
            'tipo_recargo'    => 'monto_fijo',
            'valor'           => 100,
            'acumular_mensual'=> true,
        ]);

        // 3 meses de retraso: 100 × 3 = $300
        expect($pr->calcular(2000, 3))->toBe(300.0);
    });

    test('PoliticaRecargo::calcular acumula con tope_maximo', function () {
        $ctx = crearContextoCobros();
        $pr  = crearPoliticaRecargo($ctx, [
            'tipo_recargo'    => 'monto_fijo',
            'valor'           => 100,
            'acumular_mensual'=> true,
            'tope_maximo'     => 250,
        ]);

        // 5 meses × $100 = $500, pero tope es $250
        expect($pr->calcular(2000, 5))->toBe(250.0);
    });

    test('PoliticaRecargo::aplicaEnMes retorna false para meses exentos', function () {
        $ctx = crearContextoCobros();
        $pr  = crearPoliticaRecargo($ctx, ['meses_exentos' => [1, 8]]);

        expect($pr->aplicaEnMes(1))->toBeFalse()
            ->and($pr->aplicaEnMes(8))->toBeFalse()
            ->and($pr->aplicaEnMes(9))->toBeTrue();
    });

    test('PoliticaRecargo::aplicaHoy retorna false cuando dia_limite no se ha superado', function () {
        $ctx = crearContextoCobros();
        $pr  = crearPoliticaRecargo($ctx, ['dia_limite_pago' => 20]);

        expect($pr->aplicaHoy(15))->toBeFalse()
            ->and($pr->aplicaHoy(21))->toBeTrue();
    });

    test('cargo vencido muestra recargo en pantalla de cobros', function () {
        $fechaVenc = now()->subMonths(2)->toDateString();
        $ctx       = crearContextoCobros();
        $cargo     = crearCargo($ctx, [
            'monto_original'    => 2000,
            'fecha_vencimiento' => $fechaVenc,
            'periodo'           => now()->subMonths(2)->format('Y-m'),
        ]);

        crearPoliticaRecargo($ctx, [
            'tipo_recargo'    => 'porcentaje',
            'valor'           => 10,
            'dia_limite_pago' => 1,
        ]);

        $vista      = $this->actingAs($ctx['admin'])
            ->get(route('cobros.alumno', $ctx['alumno']->id));

        $cargoVista = $vista->viewData('cargos')->first();

        expect((float) $cargoVista->recargo_calc)->toBeGreaterThan(0.0)
            ->and($cargoVista->vencido)->toBeTrue();
    });

    test('cargo no vencido no genera recargo', function () {
        $ctx   = crearContextoCobros();
        $cargo = crearCargo($ctx, [
            'fecha_vencimiento' => now()->addDays(20)->toDateString(),
            'periodo'           => now()->format('Y-m'),
        ]);

        crearPoliticaRecargo($ctx, ['tipo_recargo' => 'porcentaje', 'valor' => 10, 'dia_limite_pago' => 1]);

        $vista      = $this->actingAs($ctx['admin'])
            ->get(route('cobros.alumno', $ctx['alumno']->id));

        $cargoVista = $vista->viewData('cargos')->first();
        expect((float) $cargoVista->recargo_calc)->toBe(0.0);
    });

    test('cargo en mes exento no genera recargo aunque esté vencido', function () {
        $fechaVenc = now()->setMonth(1)->setDay(5)->subYear()->toDateString();
        $periodo   = now()->subYear()->setMonth(1)->format('Y-m');

        $ctx   = crearContextoCobros();
        $cargo = crearCargo($ctx, [
            'monto_original'    => 2000,
            'fecha_vencimiento' => $fechaVenc,
            'periodo'           => $periodo,
        ]);

        crearPoliticaRecargo($ctx, [
            'tipo_recargo'    => 'porcentaje',
            'valor'           => 10,
            'dia_limite_pago' => 1,
            'meses_exentos'   => [1],
        ]);

        $vista      = $this->actingAs($ctx['admin'])
            ->get(route('cobros.alumno', $ctx['alumno']->id));

        $cargoVista = $vista->viewData('cargos')->first();
        expect((float) $cargoVista->recargo_calc)->toBe(0.0)
            ->and($cargoVista->mes_exento)->toBeTrue();
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// 5. REPORTE DE CORTE DE CAJA
// ═════════════════════════════════════════════════════════════════════════════

describe('Reporte de corte de caja', function () {
    test('el corte muestra el total cobrado del día con dos pagos', function () {
        $ctx1   = crearContextoCobros(['alumno_nombre' => 'Ana', 'alumno_ap_paterno' => 'Torres']);
        $cargo1 = crearCargo($ctx1, ['monto_original' => 1500]);

        // Segundo alumno en el mismo ciclo y plan
        $alumno2 = Alumno::create([
            'matricula'        => fake()->unique()->numerify('B####'),
            'nombre'           => 'Carlos',
            'ap_paterno'       => 'Ruiz',
            'fecha_nacimiento' => '2014-03-20',
            'estado'           => 'activo',
        ]);
        $inscripcion2 = Inscripcion::create([
            'alumno_id' => $alumno2->id,
            'ciclo_id'  => $ctx1['ciclo']->id,
            'grupo_id'  => $ctx1['grupo']->id,
            'fecha'     => '2026-08-01',
            'activo'    => true,
        ]);
        $asignacion2 = AsignacionPlan::create([
            'plan_id'      => $ctx1['plan']->id,
            'alumno_id'    => $alumno2->id,
            'origen'       => 'individual',
            'fecha_inicio' => '2026-08-01',
            'fecha_fin'    => '2027-07-31',
        ]);
        $cargo2 = Cargo::create([
            'inscripcion_id'    => $inscripcion2->id,
            'concepto_id'       => $ctx1['concepto']->id,
            'asignacion_id'     => $asignacion2->id,
            'monto_original'    => 2500,
            'fecha_vencimiento' => '2026-09-10',
            'estado'            => 'pendiente',
            'periodo'           => '2026-09',
        ]);

        $fechaPago = now()->toDateString(); // ← Usar la fecha actual para que el corte la encuentre

        // Primer pago
        $this->actingAs($ctx1['admin'])
            ->post(route('cobros.registrar'), [
                'alumno_id'  => $ctx1['alumno']->id,
                'forma_pago' => 'efectivo',
                'fecha_pago' => $fechaPago,
                'items'      => [[
                    'tipo'                  => 'cargo',
                    'cargo_id'              => $cargo1->id,
                    'monto_abonado'         => 1500,
                    'descuento_beca'        => 0,
                    'descuento_pronto_pago' => 0,
                    'descuento_otros'       => 0,
                    'recargo'               => 0,
                ]],
            ])->assertRedirect();

        // Segundo pago
        $this->actingAs($ctx1['admin'])
            ->post(route('cobros.registrar'), [
                'alumno_id'  => $alumno2->id,
                'forma_pago' => 'transferencia',
                'fecha_pago' => $fechaPago,
                'items'      => [[
                    'tipo'                  => 'cargo',
                    'cargo_id'              => $cargo2->id,
                    'monto_abonado'         => 2500,
                    'descuento_beca'        => 0,
                    'descuento_pronto_pago' => 0,
                    'descuento_otros'       => 0,
                    'recargo'               => 0,
                ]],
            ])->assertRedirect();

        expect(Pago::count())->toBe(2);

        $vista  = $this->actingAs($ctx1['admin'])
            ->get(route('pagos.corte', ['fecha' => $fechaPago]));

        $vista->assertOk();
        $resumen = $vista->viewData('resumen');

        expect($resumen['total_pagos'])->toBe(2)
            ->and((float) $resumen['total_cobrado'])->toBe(4000.0);
    });

    test('el corte agrupa correctamente por forma de pago', function () {
        $ctx       = crearContextoCobros();
        $cargo     = crearCargo($ctx);
        $fechaPago = now()->toDateString();

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo, [
                'fecha_pago'    => $fechaPago,
                'forma_pago'    => 'tarjeta_credito',
                'monto_abonado' => 2000,
            ]))->assertRedirect();

        expect(Pago::count())->toBe(1);

        $vista   = $this->actingAs($ctx['admin'])
            ->get(route('pagos.corte', ['fecha' => $fechaPago]));

        $resumen = $vista->viewData('resumen');

        expect($resumen['por_forma_pago'])->toHaveKey('tarjeta_credito')
            ->and((float) $resumen['por_forma_pago']['tarjeta_credito']['total'])->toBe(2000.0)
            ->and($resumen['por_forma_pago']['tarjeta_credito']['cantidad'])->toBe(1);
    });

    test('el corte no incluye pagos anulados en el total cobrado', function () {
        $ctx       = crearContextoCobros();
        $cargo     = crearCargo($ctx);
        $fechaPago = now()->toDateString();

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo, [
                'fecha_pago'    => $fechaPago,
                'monto_abonado' => 2000,
            ]))->assertRedirect();

        expect(Pago::count())->toBe(1);

        $pago = Pago::first();

        $this->actingAs($ctx['admin'])
            ->post(route('pagos.anular', $pago->id), [
                'motivo' => 'Error de captura en caja',
            ])->assertRedirect();

        $vista   = $this->actingAs($ctx['admin'])
            ->get(route('pagos.corte', ['fecha' => $fechaPago]));

        $resumen = $vista->viewData('resumen');

        expect($resumen['total_pagos'])->toBe(0)
            ->and((float) $resumen['total_cobrado'])->toBe(0.0)
            ->and($resumen['total_anulados'])->toBe(1);
    });

    test('el corte muestra el nombre y grado del alumno en cada pago', function () {
        $ctx       = crearContextoCobros([
            'alumno_nombre'     => 'Sofía',
            'alumno_ap_paterno' => 'Mendoza',
            'grado_numero'      => 4,
            'grupo_nombre'      => 'A',
        ]);
        $cargo     = crearCargo($ctx);
        $fechaPago = now()->toDateString();

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo, [
                'fecha_pago'    => $fechaPago,
                'monto_abonado' => 2000,
            ]))->assertRedirect();

        expect(Pago::count())->toBe(1);

        $vista = $this->actingAs($ctx['admin'])
            ->get(route('pagos.corte', ['fecha' => $fechaPago]));

        $vista->assertOk();

        $pagos       = $vista->viewData('pagos');
        $pagoDetalle = $pagos->first()->detalles->first();

        $alumnoEnPago = $pagoDetalle->cargo->inscripcion->alumno;
        expect($alumnoEnPago->nombre)->toBe('Sofía')
            ->and($alumnoEnPago->ap_paterno)->toBe('Mendoza');

        $gradoEnPago = $pagoDetalle->cargo->inscripcion->grupo->grado;
        expect($gradoEnPago->numero)->toBe(4);
    });

    test('el folio de recibo tiene el formato correcto R{YYYYMMDD}-{NNNN}', function () {
        $ctx   = crearContextoCobros();
        $cargo = crearCargo($ctx);

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo))
            ->assertRedirect();

        $pago = Pago::first();
        expect($pago->folio_recibo)->toMatch('/^R\d{8}-\d{4}$/');
    });

    test('folios consecutivos del mismo dia se incrementan correctamente', function () {
        $ctx = crearContextoCobros();
        $c1  = crearCargo($ctx, ['periodo' => '2026-09', 'fecha_vencimiento' => '2026-09-10']);
        $c2  = crearCargo($ctx, ['periodo' => '2026-10', 'fecha_vencimiento' => '2026-10-10']);

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $c1))
            ->assertRedirect();

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $c2))
            ->assertRedirect();

        $folios = Pago::orderBy('id')->pluck('folio_recibo');

        expect($folios[0])->toEndWith('-0001')
            ->and($folios[1])->toEndWith('-0002');
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// 6. CONSISTENCIA EN HISTORIAL DE PAGOS
// ═════════════════════════════════════════════════════════════════════════════

describe('Consistencia en historial de pagos', function () {
    test('anular un pago revierte el estado del cargo a pendiente', function () {
        $ctx   = crearContextoCobros();
        $cargo = crearCargo($ctx);

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo))
            ->assertRedirect();

        $cargo->refresh();
        expect($cargo->estado)->toBe('pagado');

        $pago = Pago::first();
        $this->actingAs($ctx['admin'])
            ->post(route('pagos.anular', $pago->id), [
                'motivo' => 'Corrección de captura realizada',
            ])->assertRedirect();

        $cargo->refresh();
        expect($cargo->estado)->toBe('pendiente');
    });

    test('anular pago parcial deja el cargo en pendiente si no hay otros abonos vigentes', function () {
        $ctx   = crearContextoCobros(['aplica_beca' => false, 'aplica_recargo' => false]);
        $cargo = crearCargo($ctx, ['monto_original' => 3000]);

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo, ['monto_abonado' => 1000]))
            ->assertRedirect();

        $cargo->refresh();
        expect($cargo->estado)->toBe('parcial');

        $pago = Pago::first();
        $this->actingAs($ctx['admin'])
            ->post(route('pagos.anular', $pago->id), [
                'motivo' => 'Corrección de captura realizada',
            ])->assertRedirect();

        $cargo->refresh();
        expect($cargo->estado)->toBe('pendiente');
    });

    test('saldo_abonado solo cuenta pagos vigentes', function () {
        $ctx   = crearContextoCobros(['aplica_beca' => false, 'aplica_recargo' => false]);
        $cargo = crearCargo($ctx, ['monto_original' => 3000]);

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo, ['monto_abonado' => 3000]))
            ->assertRedirect();

        $cargo->refresh();
        expect($cargo->saldo_abonado)->toBe(3000.0);

        $pago = Pago::first();
        $this->actingAs($ctx['admin'])
            ->post(route('pagos.anular', $pago->id), [
                'motivo' => 'Reversión solicitada por el tutor',
            ])->assertRedirect();

        $cargo->refresh();
        expect($cargo->saldo_abonado)->toBe(0.0);
    });

    test('monto_cubierto equivale a saldo_abonado sin duplicar descuentos', function () {
        $ctx   = crearContextoCobros(['aplica_beca' => false, 'aplica_recargo' => false]);
        $cargo = crearCargo($ctx, ['monto_original' => 2000]);

        DescuentoCargo::create([
            'cargo_id'       => $cargo->id,
            'tipo'           => 'monto_fijo',
            'valor'          => 500,
            'monto_aplicado' => 500,
            'motivo'         => 'Descuento especial aprobado',
            'autorizado_por' => $ctx['admin']->id,
            'creado_por'     => $ctx['admin']->id,
        ]);

        // Abono = efectivo (1000) + condonación (500)
        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo, [
                'monto_abonado'   => 1500,
                'descuento_otros' => 500,
            ]))->assertRedirect();

        $cargo->refresh();
        // monto_cubierto debe ser = saldo_abonado (1500), NO 1500+500=2000
        expect($cargo->monto_cubierto)->toBe($cargo->saldo_abonado)
            ->and($cargo->monto_cubierto)->toBe(1500.0)
            ->and($cargo->estado)->toBe('parcial');
    });

    test('reporte de pagos incluye total cobrado solo de pagos vigentes', function () {
        $ctx       = crearContextoCobros();
        $cargo     = crearCargo($ctx);
        $fechaPago = now()->toDateString();

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo, [
                'fecha_pago'    => $fechaPago,
                'monto_abonado' => 2000,
            ]))->assertRedirect();

        $vista   = $this->actingAs($ctx['admin'])
            ->get(route('pagos.index'));

        $resumen = $vista->viewData('resumen');
        expect((float) $resumen['total_cobrado'])->toBe(2000.0)
            ->and($resumen['vigentes'])->toBe(1);
    });

    test('reporte detalle_ingresos excluye pagos anulados', function () {
        $ctx       = crearContextoCobros();
        $cargo     = crearCargo($ctx);
        $fechaPago = now()->toDateString();

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo, [
                'fecha_pago'    => $fechaPago,
                'monto_abonado' => 2000,
            ]))->assertRedirect();

        $pago = Pago::first();
        $this->actingAs($ctx['admin'])
            ->post(route('pagos.anular', $pago->id), [
                'motivo' => 'Reversión solicitada por el tutor',
            ])->assertRedirect();

        $vista = $this->actingAs($ctx['admin'])
            ->get(route('pagos.detalle-ingresos', [
                'fecha_desde' => $fechaPago,
                'fecha_hasta' => $fechaPago,
            ]));

        $vista->assertOk();
        $resumen = $vista->viewData('resumen');
        expect((float) $resumen['total_cobrado'])->toBe(0.0);
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// 7. ESTADO DE CUENTA DEL ALUMNO
// ═════════════════════════════════════════════════════════════════════════════

describe('Estado de cuenta del alumno', function () {
    test('estado_real de cargo pagado se calcula correctamente', function () {
        $ctx   = crearContextoCobros();
        $cargo = crearCargo($ctx);

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo))
            ->assertRedirect();

        $cargo->refresh();
        expect($cargo->estado_real)->toBe('pagado');
    });

    test('estado_real de cargo vencido no se almacena en BD como vencido', function () {
        $ctx   = crearContextoCobros();
        $cargo = crearCargo($ctx, ['fecha_vencimiento' => now()->subDays(5)->toDateString()]);

        expect($cargo->estado)->toBe('pendiente')
            ->and($cargo->estado_real)->toBe('vencido');
    });

    test('estado_real de cargo parcialmente pagado y vencido es parcial_vencido', function () {
        $ctx   = crearContextoCobros(['aplica_beca' => false, 'aplica_recargo' => false]);
        $cargo = crearCargo($ctx, [
            'monto_original'    => 3000,
            'fecha_vencimiento' => now()->subDays(5)->toDateString(),
        ]);

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo, ['monto_abonado' => 1000]))
            ->assertRedirect();

        $cargo->refresh();
        expect($cargo->estado)->toBe('parcial')
            ->and($cargo->estado_real)->toBe('parcial_vencido');
    });

    test('estado de cuenta muestra saldo_pendiente correcto excluyendo cargos pagados', function () {
        $ctx = crearContextoCobros(['aplica_beca' => false, 'aplica_recargo' => false]);
        $c1  = crearCargo($ctx, ['monto_original' => 2000, 'periodo' => '2026-09', 'fecha_vencimiento' => '2026-09-10']);
        $c2  = crearCargo($ctx, ['monto_original' => 2000, 'periodo' => '2026-10', 'fecha_vencimiento' => '2026-10-10']);

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $c1, ['monto_abonado' => 2000]))
            ->assertRedirect();

        $vista   = $this->actingAs($ctx['admin'])
            ->get(route('alumnos.estado-cuenta', $ctx['alumno']->id));

        $vista->assertOk();
        $resumen = $vista->viewData('resumen');

        expect((float) $resumen['saldo_pendiente'])->toBe(2000.0);
    });

    test('estado de cuenta descuenta beca del total_a_pagar_hoy', function () {
        $ctx   = crearContextoCobros(['aplica_beca' => true, 'aplica_recargo' => false]);
        $cargo = crearCargo($ctx, ['monto_original' => 2000, 'fecha_vencimiento' => now()->addDays(20)->toDateString()]);

        $catalogo = CatalogoBeca::create([
            'nombre' => 'Beca hermanos',
            'tipo'   => 'porcentaje',
            'valor'  => 25,
            'activo' => true,
        ]);

        BecaAlumno::create([
            'catalogo_beca_id' => $catalogo->id,
            'alumno_id'        => $ctx['alumno']->id,
            'ciclo_id'         => $ctx['ciclo']->id,
            'plan_id'          => $ctx['plan']->id,
            'vigencia_inicio'  => '2026-08-01',
            'activo'           => true,
        ]);

        $vista   = $this->actingAs($ctx['admin'])
            ->get(route('alumnos.estado-cuenta', $ctx['alumno']->id));

        $resumen = $vista->viewData('resumen');

        // saldo_pendiente = 2000, beca 25% = 500 → total_a_pagar_hoy = 1500
        expect((float) $resumen['saldo_pendiente'])->toBe(2000.0)
            ->and((float) $resumen['total_a_pagar_hoy'])->toBe(1500.0);
    });

    test('estado de cuenta incluye recargo en total_a_pagar_hoy para cargo vencido', function () {
        $fechaVenc = now()->subMonths(2)->toDateString();
        $periodo   = now()->subMonths(2)->format('Y-m');

        $ctx   = crearContextoCobros(['aplica_beca' => false, 'aplica_recargo' => true]);
        $cargo = crearCargo($ctx, [
            'monto_original'    => 2000,
            'fecha_vencimiento' => $fechaVenc,
            'periodo'           => $periodo,
        ]);

        PoliticaRecargo::create([
            'plan_id'          => $ctx['plan']->id,
            'dia_limite_pago'  => 1,
            'tipo_recargo'     => 'monto_fijo',
            'valor'            => 200,
            'tope_maximo'      => null,
            'activo'           => true,
            'acumular_mensual' => false,
            'meses_exentos'    => [],
        ]);

        $vista   = $this->actingAs($ctx['admin'])
            ->get(route('alumnos.estado-cuenta', $ctx['alumno']->id));

        $resumen = $vista->viewData('resumen');

        expect((float) $resumen['total_recargos'])->toBe(200.0)
            ->and((float) $resumen['total_a_pagar_hoy'])->toBe(2200.0);
    });

    test('estado de cuenta con descuento por pronto pago reduce total_a_pagar_hoy', function () {
        $ctx   = crearContextoCobros(['aplica_beca' => false, 'aplica_recargo' => false]);
        $cargo = crearCargo($ctx, [
            'monto_original'    => 2000,
            'fecha_vencimiento' => now()->addDays(20)->toDateString(),
            'periodo'           => now()->format('Y-m'),
        ]);

        PoliticaDescuento::create([
            'plan_id'    => $ctx['plan']->id,
            'nombre'     => 'Pronto pago mensual',
            'tipo_valor' => 'porcentaje',
            'valor'      => 5,
            'dia_limite' => 31,
            'activo'     => true,
        ]);

        $vista   = $this->actingAs($ctx['admin'])
            ->get(route('alumnos.estado-cuenta', $ctx['alumno']->id));

        $resumen = $vista->viewData('resumen');

        // 5% de $2000 = $100 de descuento → total_a_pagar_hoy = $1900
        expect((float) $resumen['total_descuentos'])->toBe(100.0)
            ->and((float) $resumen['total_a_pagar_hoy'])->toBe(1900.0);
    });

    test('estado de cuenta no muestra deuda para alumno sin cargos', function () {
        $ctx  = crearContextoCobros();

        $vista   = $this->actingAs($ctx['admin'])
            ->get(route('alumnos.estado-cuenta', $ctx['alumno']->id));

        $vista->assertOk();
        $resumen = $vista->viewData('resumen');

        expect((float) $resumen['saldo_pendiente'])->toBe(0.0)
            ->and((float) $resumen['total_a_pagar_hoy'])->toBe(0.0);
    });

    test('saldo_pendiente_base del cargo se calcula sobre monto_original menos abonado', function () {
        $ctx   = crearContextoCobros(['aplica_beca' => false, 'aplica_recargo' => false]);
        $cargo = crearCargo($ctx, ['monto_original' => 3000]);

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo, ['monto_abonado' => 1200]))
            ->assertRedirect();

        $cargo->refresh();
        expect($cargo->saldo_pendiente_base)->toBe(1800.0);
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// 8. DESCUENTOS POR PRONTO PAGO (PoliticaDescuento)
// ═════════════════════════════════════════════════════════════════════════════

describe('Descuentos por pronto pago (PoliticaDescuento)', function () {
    test('PoliticaDescuento::calcular aplica porcentaje correctamente', function () {
        $ctx = crearContextoCobros();
        $pd  = PoliticaDescuento::create([
            'plan_id'    => $ctx['plan']->id,
            'nombre'     => 'Pronto pago 5%',
            'tipo_valor' => 'porcentaje',
            'valor'      => 5,
            'dia_limite' => null,
            'activo'     => true,
        ]);

        expect($pd->calcular(2000))->toBe(100.0);
    });

    test('PoliticaDescuento::calcular aplica monto_fijo correctamente', function () {
        $ctx = crearContextoCobros();
        $pd  = PoliticaDescuento::create([
            'plan_id'    => $ctx['plan']->id,
            'nombre'     => 'Descuento fijo mensual',
            'tipo_valor' => 'monto_fijo',
            'valor'      => 150,
            'dia_limite' => null,
            'activo'     => true,
        ]);

        expect($pd->calcular(2000))->toBe(150.0);
    });

    test('PoliticaDescuento::calcular monto_fijo no excede el monto base', function () {
        $ctx = crearContextoCobros();
        $pd  = PoliticaDescuento::create([
            'plan_id'    => $ctx['plan']->id,
            'nombre'     => 'Descuento mayor al cargo',
            'tipo_valor' => 'monto_fijo',
            'valor'      => 5000,
            'dia_limite' => null,
            'activo'     => true,
        ]);

        expect($pd->calcular(500))->toBe(500.0);
    });

    test('PoliticaDescuento::aplicaHoy retorna true cuando no hay dia_limite', function () {
        $ctx = crearContextoCobros();
        $pd  = PoliticaDescuento::create([
            'plan_id'    => $ctx['plan']->id,
            'nombre'     => 'Descuento sin límite',
            'tipo_valor' => 'porcentaje',
            'valor'      => 5,
            'dia_limite' => null,
            'activo'     => true,
        ]);

        expect($pd->aplicaHoy())->toBeTrue();
    });

    test('PoliticaDescuento::aplicaHoy retorna false cuando esta inactiva', function () {
        $ctx = crearContextoCobros();
        $pd  = PoliticaDescuento::create([
            'plan_id'    => $ctx['plan']->id,
            'nombre'     => 'Descuento inactivo',
            'tipo_valor' => 'porcentaje',
            'valor'      => 5,
            'dia_limite' => null,
            'activo'     => false,
        ]);

        expect($pd->aplicaHoy())->toBeFalse();
    });
});

describe('Formas de pago tarjeta y deposito', function () {
    test('deposito es aceptado como forma de pago válida', function () {
        $ctx   = crearContextoCobros();
        $cargo = crearCargo($ctx);

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo, [
                'forma_pago'    => 'deposito',
                'monto_abonado' => 2000,
            ]))->assertRedirect();

        expect(Pago::count())->toBe(1)
            ->and(Pago::first()->forma_pago)->toBe('deposito');
    });

    test('tarjeta_credito es aceptado como forma de pago válida', function () {
        $ctx   = crearContextoCobros();
        $cargo = crearCargo($ctx);

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo, [
                'forma_pago'    => 'tarjeta_credito',
                'monto_abonado' => 2000,
            ]))->assertRedirect();

        expect(Pago::count())->toBe(1)
            ->and(Pago::first()->forma_pago)->toBe('tarjeta_credito');
    });

    test('tarjeta_debito es aceptado como forma de pago válida', function () {
        $ctx   = crearContextoCobros();
        $cargo = crearCargo($ctx);

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo, [
                'forma_pago'    => 'tarjeta_debito',
                'monto_abonado' => 2000,
            ]))->assertRedirect();

        expect(Pago::count())->toBe(1)
            ->and(Pago::first()->forma_pago)->toBe('tarjeta_debito');
    });

    test('tarjeta generico es rechazado como forma de pago', function () {
        $ctx   = crearContextoCobros();
        $cargo = crearCargo($ctx);

        $this->actingAs($ctx['admin'])
            ->post(route('cobros.registrar'), datosRegistrarPago($ctx, $cargo, [
                'forma_pago'    => 'tarjeta',
                'monto_abonado' => 2000,
            ]))->assertSessionHasErrors('forma_pago');

        expect(Pago::count())->toBe(0);
    });
});
