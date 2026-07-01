<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventoAdmisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $evento,
        public readonly array $datos,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->datos['asunto']);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.evento-admision');
    }
}
