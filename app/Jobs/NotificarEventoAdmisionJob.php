<?php

namespace App\Jobs;

use App\Mail\EventoAdmisionMail;
use App\Models\Usuario;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

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
                Mail::to($u->email)->send(new EventoAdmisionMail($this->evento, $this->datos))
            );
    }
}
