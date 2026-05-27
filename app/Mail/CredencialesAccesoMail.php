<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CredencialesAccesoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $datosUsuario;

    public function __construct($datosUsuario)
    {
        $this->datosUsuario = $datosUsuario;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tus credenciales de acceso al Portal Escolar',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.credenciales', // Esta es la vista que crearemos en el paso 3
        );
    }
}