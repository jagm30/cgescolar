<?php

namespace App\Jobs;

use App\Mail\EventoAdmisionMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

/**
 * Envía la notificación de un evento de admisión a un único destinatario.
 * Despachadо por NotificarEventoAdmisionJob (fan-out) para que un fallo
 * individual no afecte a los demás destinatarios.
 */
class EnviarCorreoAdmisionJob implements ShouldQueue
{
    use Queueable;

    /** Número máximo de intentos antes de marcar el job como fallido. */
    public int $tries = 3;

    /** Segundos de espera entre reintentos (backoff exponencial). */
    public array $backoff = [60, 300];

    public function __construct(
        private readonly string $email,
        private readonly string $evento,
        private readonly array $datos,
    ) {}

    public function handle(): void
    {
        Mail::to($this->email)->send(new EventoAdmisionMail($this->evento, $this->datos));
    }
}
