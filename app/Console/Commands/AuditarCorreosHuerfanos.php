<?php

namespace App\Console\Commands;

use App\Models\ContactoFamiliar;
use App\Models\Usuario;
use Illuminate\Console\Command;

/**
 * Detecta usuarios cuyo correo no existe en ningún contacto familiar.
 *
 * Casos que reporta:
 *   1. Usuarios con email que no aparece en ningún registro de contacto_familiar.
 *   2. Usuarios 'padre' sin contacto familiar que los tenga vinculados (usuario_id).
 *   3. Usuarios 'padre' cuyo contacto vinculado tiene un email distinto (correo desactualizado).
 */
class AuditarCorreosHuerfanos extends Command
{
    protected $signature = 'usuarios:auditar-correos
                            {--solo-padres : Solo revisa usuarios con rol padre}
                            {--exportar=   : Ruta de archivo CSV para exportar resultados}';

    protected $description = 'Busca usuarios cuyo correo no existe en la tabla de contactos familiares';

    public function handle(): int
    {
        $this->info('Auditando correos de usuarios vs contactos familiares...');
        $this->newLine();

        $soloPadres = $this->option('solo-padres');

        $query = Usuario::query()->orderBy('rol')->orderBy('nombre');

        if ($soloPadres) {
            $query->where('rol', 'padre');
        }

        $usuarios = $query->get();

        // Todos los emails registrados en contacto_familiar (flip para búsqueda O(1))
        $emailsContactos = ContactoFamiliar::whereNotNull('email')
            ->pluck('email')
            ->map(fn($e) => strtolower(trim($e)))
            ->flip();

        // Mapa: usuario_id => contacto que lo tiene vinculado
        $vinculados = ContactoFamiliar::whereNotNull('usuario_id')
            ->get(['usuario_id', 'email'])
            ->keyBy('usuario_id');

        $huerfanos          = [];  // email del usuario no existe en ningún contacto
        $correoDesvinculado = [];  // padre sin usuario_id en ningún contacto
        $correoDesactual    = [];  // padre cuyo contacto vinculado tiene email distinto

        foreach ($usuarios as $u) {
            $emailNorm         = strtolower(trim($u->email));
            $existeEnContactos = isset($emailsContactos[$emailNorm]);
            $contactoVinculado = $vinculados->get($u->id);

            if ($u->rol === 'padre') {
                if (!$contactoVinculado) {
                    $correoDesvinculado[] = $u;
                } elseif (strtolower(trim($contactoVinculado->email)) !== $emailNorm) {
                    $correoDesactual[] = [
                        'usuario'        => $u,
                        'email_contacto' => $contactoVinculado->email,
                    ];
                }
            }

            if (!$existeEnContactos) {
                $huerfanos[] = $u;
            }
        }

        // ── Tabla 1: Email no aparece en ningún contacto ──────────────────────
        $this->components->twoColumnDetail(
            '<fg=yellow;options=bold>Usuarios sin email en contacto_familiar</>',
            '<fg=yellow>' . count($huerfanos) . ' encontrado(s)</>'
        );

        if (!empty($huerfanos)) {
            $this->table(
                ['ID', 'Nombre', 'Email', 'Rol', 'Activo'],
                collect($huerfanos)->map(fn($u) => [
                    $u->id,
                    $u->nombre,
                    $u->email,
                    $u->rol,
                    $u->activo ? 'Sí' : 'No',
                ])->toArray()
            );
        } else {
            $this->line('  <fg=green>✓ Todos los correos de usuarios existen en contacto_familiar.</>');
        }

        $this->newLine();

        // ── Tabla 2: Padres sin contacto vinculado ────────────────────────────
        $this->components->twoColumnDetail(
            '<fg=yellow;options=bold>Padres sin contacto que los tenga vinculados (usuario_id)</>',
            '<fg=yellow>' . count($correoDesvinculado) . ' encontrado(s)</>'
        );

        if (!empty($correoDesvinculado)) {
            $this->table(
                ['ID', 'Nombre', 'Email', 'Activo'],
                collect($correoDesvinculado)->map(fn($u) => [
                    $u->id,
                    $u->nombre,
                    $u->email,
                    $u->activo ? 'Sí' : 'No',
                ])->toArray()
            );
        } else {
            $this->line('  <fg=green>✓ Todos los padres tienen contacto vinculado.</>');
        }

        $this->newLine();

        // ── Tabla 3: Padres con correo desactualizado ─────────────────────────
        $this->components->twoColumnDetail(
            '<fg=yellow;options=bold>Padres con correo desactualizado (usuario ≠ contacto)</>',
            '<fg=yellow>' . count($correoDesactual) . ' encontrado(s)</>'
        );

        if (!empty($correoDesactual)) {
            $this->table(
                ['ID', 'Nombre', 'Email en usuario', 'Email en contacto', 'Activo'],
                collect($correoDesactual)->map(fn($r) => [
                    $r['usuario']->id,
                    $r['usuario']->nombre,
                    $r['usuario']->email,
                    $r['email_contacto'],
                    $r['usuario']->activo ? 'Sí' : 'No',
                ])->toArray()
            );
        } else {
            $this->line('  <fg=green>✓ Todos los correos de padres coinciden con su contacto vinculado.</>');
        }

        // ── Exportar CSV ───────────────────────────────────────────────────────
        if ($rutaCsv = $this->option('exportar')) {
            $this->exportarCsv($rutaCsv, $huerfanos, $correoDesvinculado, $correoDesactual);
        }

        $totalProblemas = count($huerfanos) + count($correoDesvinculado) + count($correoDesactual);

        $this->newLine();

        if ($totalProblemas === 0) {
            $this->info('✓ Auditoría completada. No se encontraron inconsistencias.');
        } else {
            $this->warn("Auditoría completada. {$totalProblemas} inconsistencia(s) detectada(s).");
        }

        return $totalProblemas > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function exportarCsv(
        string $ruta,
        array $huerfanos,
        array $desvinculados,
        array $desactualizados
    ): void {
        $filas = [['tipo', 'usuario_id', 'nombre', 'email_usuario', 'email_contacto', 'rol', 'activo']];

        foreach ($huerfanos as $u) {
            $filas[] = ['sin_email_en_contactos', $u->id, $u->nombre, $u->email, '', $u->rol, $u->activo ? '1' : '0'];
        }

        foreach ($desvinculados as $u) {
            $filas[] = ['padre_sin_contacto_vinculado', $u->id, $u->nombre, $u->email, '', $u->rol, $u->activo ? '1' : '0'];
        }

        foreach ($desactualizados as $r) {
            $u = $r['usuario'];
            $filas[] = ['correo_desactualizado', $u->id, $u->nombre, $u->email, $r['email_contacto'], $u->rol, $u->activo ? '1' : '0'];
        }

        $fp = fopen($ruta, 'w');
        foreach ($filas as $fila) {
            fputcsv($fp, $fila);
        }
        fclose($fp);

        $this->info("CSV exportado en: {$ruta}");
    }
}
