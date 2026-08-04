<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Ejecuta todos los seeders en el orden correcto respetando
     * dependencias entre tablas.
     *
     * Uso:
     *   php artisan db:seed
     *   php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        $this->call([
            // ── 1. Estructura académica (sin dependencias) ───────────────────
            CicloEscolarSeeder::class,
            NivelEscolarSeeder::class,
            GradoSeeder::class,

            // ── 2. Usuarios y personal ───────────────────────────────────────
            UsuarioSeeder::class,
            PersonalSeeder::class,

            // ── 3. Grupos (depende de ciclo + grado) ────────────────────────
            GrupoSeeder::class,

            // ── 4. Configuración general ─────────────────────────────────────
            SettingsSeeder::class,
            ConfigFiscalSeeder::class,

            // ── 5. Catálogos financieros ─────────────────────────────────────
            CatalogoBecaSeeder::class,
            ConceptoCobroSeeder::class,
            PlanPagoSeeder::class,   // incluye plan_pago_concepto, políticas descuento/recargo

            // ── 6. Datos operativos (familia, alumnos, contactos, inscripciones)
            DatosOperativosSeeder::class,
        ]);
    }
}
