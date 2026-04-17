<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RazonSocialContactoSeeder extends Seeder
{
    /**
     * Siembra razones sociales (datos de facturación SAT) para los
     * contactos familiares existentes.
     *
     * RFC formato:
     *   Persona física  → 4 letras + 6 fecha (AAMMDD) + 3 homoclave = 13 chars
     *   Persona moral   → 3 letras + 6 fecha                + 3 homoclave = 12 chars
     *
     * registrado_por = 2 (usuario caja — "Cajero que capturó los datos")
     */
    public function run(): void
    {
        $ahora = now();

        DB::table('razon_social_contacto')->insert([

            // ── Contacto 1: Roberto López Vega (familia 1, papá) ──────────────
            // Persona física principal
            [
                'contacto_id'      => 1,
                'rfc'              => 'LOVR750312H45',
                'razon_social'     => 'Roberto López Vega',
                'regimen_fiscal'   => '605',   // Sueldos y Salarios
                'domicilio_fiscal' => '44100',  // Guadalajara, Jal.
                'uso_cfdi_default' => 'D10',   // Servicios educativos
                'es_principal'     => true,
                'registrado_por'   => 2,
                'activo'           => true,
                'creado_at'        => $ahora,
            ],
            // Persona moral (negocio propio)
            [
                'contacto_id'      => 1,
                'rfc'              => 'SLV200615AB3',
                'razon_social'     => 'Servicios López Vega S.A. de C.V.',
                'regimen_fiscal'   => '601',   // General de Ley Personas Morales
                'domicilio_fiscal' => '44100',
                'uso_cfdi_default' => 'G03',   // Gastos en general
                'es_principal'     => false,
                'registrado_por'   => 2,
                'activo'           => true,
                'creado_at'        => $ahora,
            ],

            // ── Contacto 2: María García Ruiz (familia 1, mamá) ───────────────
            [
                'contacto_id'      => 2,
                'rfc'              => 'GARM820518MJ7',
                'razon_social'     => 'María García Ruiz',
                'regimen_fiscal'   => '612',   // Personas Físicas con Actividades Empresariales
                'domicilio_fiscal' => '44200',
                'uso_cfdi_default' => 'D10',
                'es_principal'     => true,
                'registrado_por'   => 2,
                'activo'           => true,
                'creado_at'        => $ahora,
            ],

            // ── Contacto 3: Carmen Vega Torres (familia 1, abuela) ────────────
            [
                'contacto_id'      => 3,
                'rfc'              => 'VETC480920F38',
                'razon_social'     => 'Carmen Vega Torres',
                'regimen_fiscal'   => '605',
                'domicilio_fiscal' => '44600',
                'uso_cfdi_default' => 'D10',
                'es_principal'     => true,
                'registrado_por'   => 2,
                'activo'           => true,
                'creado_at'        => $ahora,
            ],

            // ── Contacto 4: Carlos Martínez Soto (familia 2, papá) ────────────
            [
                'contacto_id'      => 4,
                'rfc'              => 'MASC680904H72',
                'razon_social'     => 'Carlos Martínez Soto',
                'regimen_fiscal'   => '606',   // Arrendamiento
                'domicilio_fiscal' => '45070',  // Zapopan, Jal.
                'uso_cfdi_default' => 'D10',
                'es_principal'     => true,
                'registrado_por'   => 2,
                'activo'           => true,
                'creado_at'        => $ahora,
            ],

            // ── Contacto 5: Laura Ruiz Mendoza (familia 2, mamá) ──────────────
            [
                'contacto_id'      => 5,
                'rfc'              => 'RUML900227KP4',
                'razon_social'     => 'Laura Ruiz Mendoza',
                'regimen_fiscal'   => '626',   // Régimen Simplificado de Confianza (RESICO)
                'domicilio_fiscal' => '45070',
                'uso_cfdi_default' => 'D10',
                'es_principal'     => true,
                'registrado_por'   => 2,
                'activo'           => true,
                'creado_at'        => $ahora,
            ],

            // ── Contacto 6: Patricia Cruz Lara (familia 3, mamá) ──────────────
            [
                'contacto_id'      => 6,
                'rfc'              => 'CULP780614D56',
                'razon_social'     => 'Patricia Cruz Lara',
                'regimen_fiscal'   => '605',
                'domicilio_fiscal' => '45500',  // Tlaquepaque, Jal.
                'uso_cfdi_default' => 'D10',
                'es_principal'     => true,
                'registrado_por'   => 2,
                'activo'           => true,
                'creado_at'        => $ahora,
            ],

            // ── Contacto 7: Elena Sánchez Peña (familia 4, mamá) ──────────────
            [
                'contacto_id'      => 7,
                'rfc'              => 'SAPE850310QR1',
                'razon_social'     => 'Elena Sánchez Peña',
                'regimen_fiscal'   => '612',
                'domicilio_fiscal' => '45400',  // Tonalá, Jal.
                'uso_cfdi_default' => 'D10',
                'es_principal'     => true,
                'registrado_por'   => 2,
                'activo'           => true,
                'creado_at'        => $ahora,
            ],
        ]);
    }
}
