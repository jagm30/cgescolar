<?php

use App\Models\Alumno;
use App\Models\BecaAlumno;
use App\Models\CatalogoBeca;
use App\Models\CicloEscolar;
use App\Models\ConceptoCobro;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('administrador puede asignar una beca a un alumno', function () {
    $admin = Usuario::create([
        'nombre' => 'Admin',
        'email' => 'admin@example.com',
        'password_hash' => bcrypt('password'),
        'rol' => 'administrador',
        'activo' => true,
    ]);

    $ciclo = CicloEscolar::create([
        'nombre' => '2025-2026',
        'fecha_inicio' => now()->format('Y-m-d'),
        'fecha_fin' => now()->addYear()->format('Y-m-d'),
        'estado' => 'activo',
    ]);

    $alumno = Alumno::create([
        'matricula' => 'A001',
        'nombre' => 'María',
        'ap_paterno' => 'Pérez',
        'fecha_nacimiento' => now()->subYears(10)->format('Y-m-d'),
    ]);

    $concepto = ConceptoCobro::create([
        'nombre' => 'Colegiatura',
        'tipo' => 'colegiatura',
        'aplica_beca' => true,
        'activo' => true,
    ]);

    $catalogo = CatalogoBeca::create([
        'nombre' => 'Beca del mérito',
        'descripcion' => 'Descuento especial por mérito académico.',
        'tipo' => 'porcentaje',
        'valor' => 20,
        'activo' => true,
    ]);

    $response = $this->actingAs($admin)
        ->post(route('becas.store'), [
            'catalogo_beca_id' => $catalogo->id,
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'concepto_id' => $concepto->id,
            'vigencia_inicio' => now()->format('Y-m-d'),
            'vigencia_fin' => now()->addMonths(6)->format('Y-m-d'),
            'motivo' => 'Beca de prueba',
        ]);

    $response->assertRedirect(route('becas.index'));
    $this->assertDatabaseHas('beca_alumno', [
        'alumno_id' => $alumno->id,
        'catalogo_beca_id' => $catalogo->id,
        'ciclo_id' => $ciclo->id,
        'concepto_id' => $concepto->id,
        'activo' => true,
    ]);
});

test('administrador puede reemplazar la beca activa marcando deshabilitar anterior', function () {
    $admin = Usuario::create([
        'nombre' => 'Admin',
        'email' => 'admin2@example.com',
        'password_hash' => bcrypt('password'),
        'rol' => 'administrador',
        'activo' => true,
    ]);

    $ciclo = CicloEscolar::create([
        'nombre' => '2025-2026',
        'fecha_inicio' => now()->format('Y-m-d'),
        'fecha_fin' => now()->addYear()->format('Y-m-d'),
        'estado' => 'activo',
    ]);

    $alumno = Alumno::create([
        'matricula' => 'A002',
        'nombre' => 'Luis',
        'ap_paterno' => 'Gómez',
        'fecha_nacimiento' => now()->subYears(11)->format('Y-m-d'),
    ]);

    $concepto = ConceptoCobro::create([
        'nombre' => 'Colegiatura',
        'tipo' => 'colegiatura',
        'aplica_beca' => true,
        'activo' => true,
    ]);

    $becaAnterior = CatalogoBeca::create([
        'nombre' => 'Beca anterior',
        'descripcion' => 'Beca anterior.',
        'tipo' => 'monto_fijo',
        'valor' => 500,
        'activo' => true,
    ]);

    $nuevaBeca = CatalogoBeca::create([
        'nombre' => 'Beca nueva',
        'descripcion' => 'Beca de reemplazo.',
        'tipo' => 'porcentaje',
        'valor' => 10,
        'activo' => true,
    ]);

    BecaAlumno::create([
        'catalogo_beca_id' => $becaAnterior->id,
        'alumno_id' => $alumno->id,
        'ciclo_id' => $ciclo->id,
        'concepto_id' => $concepto->id,
        'vigencia_inicio' => now()->format('Y-m-d'),
        'vigencia_fin' => now()->addMonths(6)->format('Y-m-d'),
        'activo' => true,
    ]);

    $response = $this->actingAs($admin)
        ->post(route('becas.store'), [
            'catalogo_beca_id' => $nuevaBeca->id,
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'concepto_id' => $concepto->id,
            'vigencia_inicio' => now()->format('Y-m-d'),
            'vigencia_fin' => now()->addMonths(6)->format('Y-m-d'),
            'motivo' => 'Reemplazo de beca',
            'deshabilitar_beca_anterior' => '1',
        ]);

    $response->assertRedirect(route('becas.index'));
    $this->assertDatabaseHas('beca_alumno', [
        'alumno_id' => $alumno->id,
        'catalogo_beca_id' => $becaAnterior->id,
        'activo' => false,
    ]);
    $this->assertDatabaseHas('beca_alumno', [
        'alumno_id' => $alumno->id,
        'catalogo_beca_id' => $nuevaBeca->id,
        'activo' => true,
    ]);
});
