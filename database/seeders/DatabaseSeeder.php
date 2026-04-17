<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Ejecuta todos los seeders en el orden correcto
     * respetando dependencias entre tablas.
     *
     * Uso:
     *   php artisan db:seed
     *   php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        $this->call([
            // ── Módulo 1: Estructura académica ──────────
            CicloEscolarSeeder::class,
            NivelEscolarSeeder::class,
            GradoSeeder::class,

            // ── Usuarios base (antes de contactos) ──────
            UsuarioSeeder::class,

            // ── Estructura de grupos ─────────────────────
            GrupoSeeder::class,

            // ── Módulo 2: Alumnos y familias ─────────────
            FamiliaSeeder::class,
            AlumnoSeeder::class,
            InscripcionSeeder::class,
            ContactoFamiliarSeeder::class,
            AlumnoContactoSeeder::class,
            RazonSocialContactoSeeder::class,

            // ── Módulo 3: Planes y cobros ────────────────
            ConceptoCobroSeeder::class,
            PlanPagoSeeder::class,
            AsignacionPlanSeeder::class,
            CargoSeeder::class,

            // ── Módulo 4: Becas ──────────────────────────
            CatalogoBecaSeeder::class,
            BecaAlumnoSeeder::class,

            // ── Módulo 5: Admisiones ─────────────────────
            ProspectoSeeder::class,
        ]);
    }
}
