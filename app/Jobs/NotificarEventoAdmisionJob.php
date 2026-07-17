<?php

namespace App\Jobs;

use App\Models\Usuario;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Orquestador: despacha un EnviarCorreoAdmisionJob por cada usuario de admisiones
 * activo, de modo que un fallo individual no cancele el resto de envíos.
 */
class NotificarEventoAdmisionJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $evento,
        private readonly array $datos,
    ) {}

    public function handle(): void
    {
        Usuario::activo()
            ->where('rol', 'admisiones')
            ->whereNotNull('email')
            ->each(fn (Usuario $u) =>
                EnviarCorreoAdmisionJob::dispatch($u->email, $this->evento, $this->datos)
            );
    }
}
