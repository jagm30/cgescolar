<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProspectoSeeder extends Seeder
{
    public function run(): void
    {
        // responsable_id = 3 (recepción)
        // nivel_interes_id: 3=Primaria, 4=Secundaria
        DB::table('prospecto')->insert([
            [
                'ciclo_id'              => 2,
                'nombre'                => 'Emilio',
                'fecha_nacimiento'      => '2016-06-10',
                'nivel_interes_id'      => 2,
                'contacto_nombre'       => 'Jorge Ramírez',
                'contacto_telefono'     => '5533445566',
                'contacto_email'        => 'jorge.ramirez@gmail.com',
                'canal_contacto'        => 'referido',
                'etapa'                 => 'documentacion',
                'responsable_id'        => 3,
                'fecha_primer_contacto' => '2026-01-15',
                'motivo_no_concrecion'  => null,
                'alumno_id'             => null,
                'creado_at'             => now(),
            ],
            [
                'ciclo_id'              => 2,
                'nombre'                => 'Isabella',
                'fecha_nacimiento'      => '2014-02-28',
                'nivel_interes_id'      => 3,
                'contacto_nombre'       => 'Sandra Torres',
                'contacto_telefono'     => '5522113344',
                'contacto_email'        => 'sandra.torres@gmail.com',
                'canal_contacto'        => 'web',
                'etapa'                 => 'cita',
                'responsable_id'        => 3,
                'fecha_primer_contacto' => '2026-02-03',
                'motivo_no_concrecion'  => null,
                'alumno_id'             => null,
                'creado_at'             => now(),
            ],
            [
                'ciclo_id'              => 3,
                'nombre'                => 'Mateo',
                'fecha_nacimiento'      => '2013-09-15',
                'nivel_interes_id'      => 4,
                'contacto_nombre'       => 'Fernando Díaz',
                'contacto_telefono'     => '5544556677',
                'contacto_email'        => 'fernando.diaz@gmail.com',
                'canal_contacto'        => 'visita_directa',
                'etapa'                 => 'prospecto',
                'responsable_id'        => 3,
                'fecha_primer_contacto' => '2026-03-10',
                'motivo_no_concrecion'  => null,
                'alumno_id'             => null,
                'creado_at'             => now(),
            ],
            [
                'ciclo_id'              => 2,
                'nombre'                => 'Daniela',
                'fecha_nacimiento'      => '2017-11-20',
                'nivel_interes_id'      => 2,
                'contacto_nombre'       => 'Alejandra Fuentes',
                'contacto_telefono'     => '5511998877',
                'contacto_email'        => null,
                'canal_contacto'        => 'redes',
                'etapa'                 => 'no_concretado',
                'responsable_id'        => 3,
                'fecha_primer_contacto' => '2025-12-01',
                'motivo_no_concrecion'  => 'Optó por otra institución',
                'alumno_id'             => null,
                'creado_at'             => now(),
            ],
        ]);

        // Seguimientos de los prospectos activos
        DB::table('seguimiento_admision')->insert([
            ['prospecto_id' => 1, 'usuario_id' => 3, 'fecha' => '2026-01-15', 'tipo_accion' => 'llamada',      'notas' => 'Primer contacto. Interesado en preescolar. Se agenda visita.', 'creado_at' => now()],
            ['prospecto_id' => 1, 'usuario_id' => 3, 'fecha' => '2026-01-22', 'tipo_accion' => 'visita',       'notas' => 'Familia visitó la institución. Muy interesados. Solicitaron lista de documentos.', 'creado_at' => now()],
            ['prospecto_id' => 1, 'usuario_id' => 3, 'fecha' => '2026-02-01', 'tipo_accion' => 'cambio_etapa', 'notas' => 'Entregó acta de nacimiento y CURP. Faltan cartilla de vacunación y fotos.', 'creado_at' => now()],
            ['prospecto_id' => 2, 'usuario_id' => 3, 'fecha' => '2026-02-03', 'tipo_accion' => 'email',        'notas' => 'Contacto por formulario web. Se respondió con información del colegio.', 'creado_at' => now()],
            ['prospecto_id' => 2, 'usuario_id' => 3, 'fecha' => '2026-02-10', 'tipo_accion' => 'cita',         'notas' => 'Cita programada para el 17 de febrero a las 10:00 am.', 'creado_at' => now()],
        ]);

        // Documentos requeridos para prospectos activos
        DB::table('doc_admision')->insert([
            ['prospecto_id' => 1, 'tipo_documento' => 'Acta de nacimiento',    'estado' => 'entregado'],
            ['prospecto_id' => 1, 'tipo_documento' => 'CURP',                  'estado' => 'entregado'],
            ['prospecto_id' => 1, 'tipo_documento' => 'Cartilla de vacunación','estado' => 'pendiente'],
            ['prospecto_id' => 1, 'tipo_documento' => 'Fotos tamaño infantil', 'estado' => 'pendiente'],
            ['prospecto_id' => 2, 'tipo_documento' => 'Acta de nacimiento',    'estado' => 'pendiente'],
            ['prospecto_id' => 2, 'tipo_documento' => 'CURP',                  'estado' => 'pendiente'],
            ['prospecto_id' => 2, 'tipo_documento' => 'Boletas ciclo anterior','estado' => 'pendiente'],
            ['prospecto_id' => 2, 'tipo_documento' => 'Comprobante de domicilio','estado' => 'pendiente'],
        ]);
    }
}
