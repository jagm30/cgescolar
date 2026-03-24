<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlumnoContactoSeeder extends Seeder
{
    public function run(): void
    {
        // contacto_id: 1=Roberto(papá), 2=María(mamá), 3=Carmen(abuela)
        //              4=Carlos(papá), 5=Laura(mamá), 6=Patricia(mamá)
        //              7=Elena(mamá)
        DB::table('alumno_contacto')->insert([
            // Juan López (alumno 1) — papá, mamá y abuela
            ['alumno_id' => 1, 'contacto_id' => 1, 'parentesco' => 'padre', 'tipo' => 'padre',  'orden' => 1, 'autorizado_recoger' => true,  'es_responsable_pago' => true,  'activo' => true],
            ['alumno_id' => 1, 'contacto_id' => 2, 'parentesco' => 'madre', 'tipo' => 'madre',  'orden' => 2, 'autorizado_recoger' => true,  'es_responsable_pago' => false, 'activo' => true],
            ['alumno_id' => 1, 'contacto_id' => 3, 'parentesco' => 'abuelo','tipo' => 'tercero_autorizado', 'orden' => 3, 'autorizado_recoger' => true, 'es_responsable_pago' => false, 'activo' => true],

            // Ana López (alumno 2) — mismos contactos
            ['alumno_id' => 2, 'contacto_id' => 1, 'parentesco' => 'padre', 'tipo' => 'padre',  'orden' => 1, 'autorizado_recoger' => true,  'es_responsable_pago' => true,  'activo' => true],
            ['alumno_id' => 2, 'contacto_id' => 2, 'parentesco' => 'madre', 'tipo' => 'madre',  'orden' => 2, 'autorizado_recoger' => true,  'es_responsable_pago' => false, 'activo' => true],
            ['alumno_id' => 2, 'contacto_id' => 3, 'parentesco' => 'abuelo','tipo' => 'tercero_autorizado', 'orden' => 3, 'autorizado_recoger' => true, 'es_responsable_pago' => false, 'activo' => true],

            // Luis Martínez (alumno 3) — papá y mamá
            ['alumno_id' => 3, 'contacto_id' => 4, 'parentesco' => 'padre', 'tipo' => 'padre',  'orden' => 1, 'autorizado_recoger' => true,  'es_responsable_pago' => true,  'activo' => true],
            ['alumno_id' => 3, 'contacto_id' => 5, 'parentesco' => 'madre', 'tipo' => 'madre',  'orden' => 2, 'autorizado_recoger' => true,  'es_responsable_pago' => false, 'activo' => true],

            // Sofía Martínez (alumno 4) — mismos contactos
            ['alumno_id' => 4, 'contacto_id' => 4, 'parentesco' => 'padre', 'tipo' => 'padre',  'orden' => 1, 'autorizado_recoger' => true,  'es_responsable_pago' => true,  'activo' => true],
            ['alumno_id' => 4, 'contacto_id' => 5, 'parentesco' => 'madre', 'tipo' => 'madre',  'orden' => 2, 'autorizado_recoger' => true,  'es_responsable_pago' => false, 'activo' => true],

            // Diego Hernández (alumno 5) — solo mamá
            ['alumno_id' => 5, 'contacto_id' => 6, 'parentesco' => 'madre', 'tipo' => 'madre',  'orden' => 1, 'autorizado_recoger' => true,  'es_responsable_pago' => true,  'activo' => true],

            // Valeria Sánchez (alumno 6) — solo mamá
            ['alumno_id' => 6, 'contacto_id' => 7, 'parentesco' => 'madre', 'tipo' => 'madre',  'orden' => 1, 'autorizado_recoger' => true,  'es_responsable_pago' => true,  'activo' => true],
        ]);
    }
}
