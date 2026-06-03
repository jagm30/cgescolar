<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConceptoCobroSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('concepto_cobro')->insert([
            // Colegiaturas (aplica_beca = true)
            ['nombre' => 'Colegiatura Enero',       'tipo' => 'colegiatura', 'aplica_beca' => true,  'aplica_recargo' => true,  'clave_sat' => '86121500', 'activo' => true, 'descripcion' => null],
            ['nombre' => 'Colegiatura Febrero',      'tipo' => 'colegiatura', 'aplica_beca' => true,  'aplica_recargo' => true,  'clave_sat' => '86121500', 'activo' => true, 'descripcion' => null],
            ['nombre' => 'Colegiatura Marzo',        'tipo' => 'colegiatura', 'aplica_beca' => true,  'aplica_recargo' => true,  'clave_sat' => '86121500', 'activo' => true, 'descripcion' => null],
            ['nombre' => 'Colegiatura Abril',        'tipo' => 'colegiatura', 'aplica_beca' => true,  'aplica_recargo' => true,  'clave_sat' => '86121500', 'activo' => true, 'descripcion' => null],
            ['nombre' => 'Colegiatura Mayo',         'tipo' => 'colegiatura', 'aplica_beca' => true,  'aplica_recargo' => true,  'clave_sat' => '86121500', 'activo' => true, 'descripcion' => null],
            ['nombre' => 'Colegiatura Junio',        'tipo' => 'colegiatura', 'aplica_beca' => true,  'aplica_recargo' => true,  'clave_sat' => '86121500', 'activo' => true, 'descripcion' => null],
            ['nombre' => 'Colegiatura Julio',        'tipo' => 'colegiatura', 'aplica_beca' => true,  'aplica_recargo' => true,  'clave_sat' => '86121500', 'activo' => true, 'descripcion' => null],
            ['nombre' => 'Colegiatura Agosto',       'tipo' => 'colegiatura', 'aplica_beca' => true,  'aplica_recargo' => true,  'clave_sat' => '86121500', 'activo' => true, 'descripcion' => null],
            ['nombre' => 'Colegiatura Septiembre',   'tipo' => 'colegiatura', 'aplica_beca' => true,  'aplica_recargo' => true,  'clave_sat' => '86121500', 'activo' => true, 'descripcion' => null],
            ['nombre' => 'Colegiatura Octubre',      'tipo' => 'colegiatura', 'aplica_beca' => true,  'aplica_recargo' => true,  'clave_sat' => '86121500', 'activo' => true, 'descripcion' => null],
            ['nombre' => 'Colegiatura Noviembre',    'tipo' => 'colegiatura', 'aplica_beca' => true,  'aplica_recargo' => true,  'clave_sat' => '86121500', 'activo' => true, 'descripcion' => null],
            ['nombre' => 'Colegiatura Diciembre',    'tipo' => 'colegiatura', 'aplica_beca' => true,  'aplica_recargo' => true,  'clave_sat' => '86121500', 'activo' => true, 'descripcion' => null],
            // Inscripciones
            ['nombre' => 'Inscripción Maternal',    'tipo' => 'inscripcion', 'aplica_beca' => false, 'aplica_recargo' => false, 'clave_sat' => '86121500', 'activo' => true, 'descripcion' => null],
            ['nombre' => 'Inscripción Preescolar',  'tipo' => 'inscripcion', 'aplica_beca' => false, 'aplica_recargo' => false, 'clave_sat' => '86121500', 'activo' => true, 'descripcion' => null],
            ['nombre' => 'Inscripción Primaria',    'tipo' => 'inscripcion', 'aplica_beca' => false, 'aplica_recargo' => false, 'clave_sat' => '86121500', 'activo' => true, 'descripcion' => null],
            ['nombre' => 'Inscripción Secundaria',  'tipo' => 'inscripcion', 'aplica_beca' => false, 'aplica_recargo' => false, 'clave_sat' => '86121500', 'activo' => true, 'descripcion' => null],
            // Otros
            ['nombre' => 'Material didáctico',      'tipo' => 'cargo_unico', 'aplica_beca' => false, 'aplica_recargo' => false, 'clave_sat' => '60121000', 'activo' => true, 'descripcion' => 'Pago único anual'],
            ['nombre' => 'Seguro escolar',          'tipo' => 'cargo_unico', 'aplica_beca' => false, 'aplica_recargo' => false, 'clave_sat' => '84121500', 'activo' => true, 'descripcion' => 'Seguro de accidentes'],
        ]);
    }
}
