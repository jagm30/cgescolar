<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoBecaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('catalogo_beca')->insert([
            ['nombre' => 'Beca Excelencia',  'descripcion' => 'Promedio mayor a 9.5', 'tipo' => 'porcentaje', 'valor' => 50.00, 'activo' => true, 'creado_at' => now()],
            ['nombre' => 'Beca Hermanos',    'descripcion' => 'Segundo hermano inscrito en el ciclo', 'tipo' => 'porcentaje', 'valor' => 15.00, 'activo' => true, 'creado_at' => now()],
            ['nombre' => 'Beca Trabajador',  'descripcion' => 'Hijo de empleado de la institución', 'tipo' => 'porcentaje', 'valor' => 100.00, 'activo' => true, 'creado_at' => now()],
            ['nombre' => 'Beca Especial',    'descripcion' => 'Descuento fijo autorizado por dirección', 'tipo' => 'monto_fijo', 'valor' => 500.00, 'activo' => true, 'creado_at' => now()],
        ]);
    }
}
